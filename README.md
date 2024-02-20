# Activity Logger

The ActivityLogger trait provides functionality to log changes made to an Eloquent model in a Laravel application. 
This README will guide you through the features of this package and how to effectively use it in your project.

## Technologies

- PHP 8.2
- Laravel 10

## Features

- Automatic Logging: Logs model events such as creation, update, and deletion automatically.
- Data Tracking: Tracks changes made to model attributes and logs them.
- User Identification: Logs user ID if authenticated.
- Parent Model Support: Supports logging for parent-child relationships in models.
- Request Details: Includes request details (such as parameters) in the log.
- Flexible Configuration: Easily configure which events should be logged and customize ignored fields.

## Installation

Add the repository to your Laravel project's `composer.json` file.

   
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/Ranjithkumar-Murugesan/activity-log"
        }
    ]
	

You can install the package via Composer:
	
	
    composer require ical/activity-log dev-main
	
    

## Running Migrations
Before using the ActivityLogger trait, make sure to run the migration command to create the necessary table in your database
   
	
	php artisan migrate --path=vendor/ical/activity-log/src/database/migrations
	
	
		
## Usage
Apply the Trait to Your Models: Use the ActivityLogger trait in your Laravel model classes where you want to enable activity logging.

	
	use Illuminate\Database\Eloquent\Model;
	use Ical\ActivityLog\Traits\ActivityLogger;

	class YourModel extends Model
	{
		use ActivityLogger;

		// Your model code...
	}
	
		
Set Parent Model (Optional): If your model is associated with another parent model, you can set the parent model instance using the setParentModel method.
	
	
	
	$yourModel->setParentModel($parentModel);
	
	
	
Recordable Events (Optional): By default, the trait logs created, updated, and deleted events. You can customize this behavior by defining the $recordableEvents property in your model.
	    
	
	protected static $recordableEvents = ['created', 'updated', 'deleted'];
	

## Table Columns

The activity_logs table created by the migration contains the following columns:

- id: Primary key auto-incrementing integer.
- user_id: Foreign key referencing the id column of the users table. Nullable. On deletion of the associated user, the value is set to null.
- event: String indicating the type of event (e.g., created, updated, deleted).
- model_type: String representing the class name of the model being logged.
- model_id: Unsigned big integer representing the ID of the model being logged.
- parent_model_type: Nullable string representing the class name of the parent model (if applicable).
- parent_model_id: Nullable unsigned big integer representing the ID of the parent model (if applicable).
- activity_log_id: Nullable foreign key referencing the `id` column of the `activity_logs` table. This column is used to establish a relationship between the current log entry and a previous log entry, typically representing a parent-child relationship. If a parent model is specified with `parent_model_id`, this column is used to identify the parent record for the `activity_logs` table. It allows for hierarchical tracking of activity logs, enabling you to trace back the history of changes across related models.
- old_data: JSON column storing the old data before the event.
- new_data: JSON column storing the new data after the event.
- changes: JSON column storing the changes made during the event.
- request_details: JSON column storing details about the HTTP request associated with the event.
- created_at and updated_at: Timestamps indicating the creation and last update times of the log entry.
- Indexing is applied to model_type, model_id, parent_model_type, parent_model_id columns for better query performance. 
	

	
