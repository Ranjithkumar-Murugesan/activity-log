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

## Getting Started

