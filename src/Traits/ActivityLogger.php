<?php

namespace Ical\ActivityLog\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

trait ActivityLogger
{
    const CREATED = 'created';
    const UPDATED = 'updated';
    const DELETED = 'deleted';

    /**
     * The parent model instance.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $parentModel;

    /**
     * Define ignored fields here.
     *
     * @var array
     */
    protected $ignoredFields = ['password', 'password_confirmation'];

    /**
     * Set the parent model instance.
     *
     * @param \Illuminate\Database\Eloquent\Model $parentModel
     * @return void
     */
    public function setParentModel($parentModel)
    {
        $this->parentModel = $parentModel;
    }

    /**
     * Boot the ActivityLogger trait.
     *
     * @return void
     */
    public static function bootActivityLogger()
    {
        foreach (static::getRecordableEvents() as $eventName) {
            static::$eventName(function (Model $model) use ($eventName) {
                $model->logModelEvent($eventName);
            });
        }
    }

    /**
     * Log the model event.
     *
     * @param string $eventName
     * @return void
     */
    protected function logModelEvent(string $eventName)
    {
        $oldData = $this->getOriginal();
        $newData = $this->getAttributes();
        $changes = $this->getDirty();

        if (!empty($changes) || $eventName == self::DELETED) {
            $this->removeIgnoredFields($oldData, $newData, $changes);
            $userId = $this->getUserId();

            $logData = $this->prepareLogData($eventName, $oldData, $newData, $changes, $userId);

            if (isset($this->parentModel)) {
                $this->addParentModelInfo($logData);
            }

            $this->insertLogData($logData);
        }
    }

    /**
     * Remove ignored fields from data arrays.
     *
     * @param array $oldData
     * @param array $newData
     * @param array $changes
     * @return void
     */
    protected function removeIgnoredFields(&$oldData, &$newData, &$changes)
    {
        foreach ($this->ignoredFields as $field) {
            unset($oldData[$field]);
            unset($newData[$field]);
            unset($changes[$field]);
            // Only merge the field if it exists in the request
            if (Request::has($field)) {
                Request::merge([$field => '']);
            }
        }
    }

    /**
     * Get the user ID.
     *
     * @return int|null
     */
    protected function getUserId()
    {
        return auth()->check() ? auth()->user()->id : null;
    }

    /**
     * Prepare data for logging.
     *
     * @param string $eventName
     * @param array $oldData
     * @param array $newData
     * @param array $changes
     * @param int|null $userId
     * @return array
     */
    protected function prepareLogData($eventName, $oldData, $newData, $changes, $userId)
    {
        return [
            'event' => $eventName,
            'model_type' => get_class($this),
            'model_id' => $this->getKey(),
            'old_data' => json_encode($oldData),
            'new_data' => json_encode($newData),
            'changes' => json_encode($changes),
            'request_details' => json_encode(Request::all()),
            'user_id' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Add parent model information to the log data.
     *
     * @param array $logData
     * @return void
     */
    protected function addParentModelInfo(&$logData)
    {
        $logData['parent_model_type'] = get_class($this->parentModel);
        $logData['parent_model_id'] = $this->parentModel->getKey();
        if (!empty($this->parentModel->getDirty()) || $logData['event'] == self::CREATED) {
            $lastActivityLogId = $this->getLastActivityLogId($logData);
            if ($lastActivityLogId) {
                $logData['activity_log_id'] = $lastActivityLogId;
            }
        }
    }

    /**
     * Get the last activity log ID.
     *
     * @param array $logData
     * @return int|null
     */
    protected function getLastActivityLogId($logData)
    {
        $query = DB::table('activity_logs')
            ->where('model_type', $logData['parent_model_type'])
            ->where('model_id', $logData['parent_model_id']);

        if ($logData['user_id'] !== null) {
            $query->where('user_id', $logData['user_id']);
        }

        return $query->latest('id')->value('id');
    }

    /**
     * Insert log data into the database.
     *
     * @param array $logData
     * @return void
     */
    protected function insertLogData($logData)
    {
        DB::table('activity_logs')->insertGetId($logData);
    }

    /**
     * Determine if the activity log ID should be set.
     *
     * @return bool
     */
    protected function shouldSetActivityLogId(): bool
    {
        return isset($this->setActivityLogId) ? (bool) $this->setActivityLogId : true;
    }

    /**
     * Get the recordable events for the model.
     *
     * @return array
     */
    protected static function getRecordableEvents(): array
    {
        return isset(static::$recordableEvents) ? static::$recordableEvents : [self::CREATED, self::UPDATED, self::DELETED];
    }
}
