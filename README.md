# PHP_Laravel12_Telegram

## Project Description

PHP_Laravel12_Telegram is a Laravel 12 based web application that demonstrates how to send Telegram notifications directly from a Laravel application using Laravel's built-in notification system.

This project allows the application to communicate with Telegram users through a Telegram Bot. When a specific route is accessed, Laravel sends a real-time notification message to the configured Telegram chat.

The project shows how to properly configure Telegram Bot integration, set up notification routing, and send messages programmatically using Laravel Notifications.

This implementation follows Laravel's standard structure and best practices, making it easy to understand, maintain, and extend.


## Project Features

• Send Telegram notifications from Laravel 12
• Integration with Telegram Bot API
• Uses Laravel Notification system
• Secure bot token configuration using .env
• Simple controller-based trigger system
• Clean and structured Laravel architecture
• Beginner-friendly and easy to understand
• Supports custom messages, buttons, and formatted text


## How It Works

The workflow of this project is:

1. A Telegram Bot is created and connected to the Laravel application

2. The Telegram Bot Token is stored securely in the .env file

3. The User model defines the Telegram Chat ID

4. A Notification class defines the message content

5. A Controller triggers the notification

6. When the route is accessed, Laravel sends the message to Telegram

7. The message is delivered instantly to the user's Telegram account



## Technologies Used

• PHP 8+
• Laravel 12
• MySQL (optional)
• Telegram Bot API
• Laravel Notification System


## Requirements

• PHP 8.2 or higher  
• Composer  
• Laravel 12  
• MySQL (optional)  
• Telegram account  
• Telegram Bot Token  
• Internet connection  



---



## Installation Steps


---


## STEP 1: Create Laravel 12 Project

### Open terminal / CMD and run:

```
composer create-project laravel/laravel PHP_Laravel12_Telegram "12.*"

```

### Go inside project:

```
cd PHP_Laravel12_Telegram

```

#### Explanation:

This command downloads and installs a fresh Laravel 12 application using Composer. It creates the project folder with all required files and folders.

The cd command moves into the project directory so you can run Laravel commands inside it.





## STEP 2: Database Setup (Optional)

### Open .env and set:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel12_Telegram
DB_USERNAME=root
DB_PASSWORD=

```

### Create database in MySQL / phpMyAdmin:

```
Database name: laravel12_Telegram

```

### Then Run:

```
php artisan migrate

```


#### Explanation:

This step connects your Laravel application to the MySQL database using .env configuration.

The php artisan migrate command creates default database tables required by Laravel, such as users and jobs.




## STEP 3: Install Telegram Package 

### Run command:

```
composer require laravel-notification-channels/telegram

```


#### Explanation:

This command installs the Telegram Notification Channel package, which allows Laravel to send messages to Telegram using the Notification system.

It adds all required classes and dependencies automatically.






## STEP 4: Create Telegram Bot

1. Open Telegram → search:

```
@BotFather

```

2. Send:

```
/start

```

3. Then:

```
/newbot

```

#### Example:

```
Bot Name: Laravel12 Bot
Username: laravel12_test_bot

```

#### You will get:

```
Bot Token:
123456789:ABCDEF-XXXXXXXXXXXXX

```

#### Explanation:

This step creates a Telegram Bot using BotFather, which is Telegram’s official bot creation tool.

The bot acts as a bridge between your Laravel application and Telegram users.





## STEP 5: Add Token in .env

### Open: .env

#### Add:

```
TELEGRAM_BOT_TOKEN=123456789:ABCDEF-XXXXXXXXXXXXX

```

#### Explanation:

The bot token is stored securely in the .env file so it is not exposed publicly.

Laravel uses this token to authenticate and send messages through the Telegram Bot API.






## STEP 6: Configure services.php 

### Open: config/services.php

#### Add:

```
'telegram-bot-api' => [
        'token' => env('TELEGRAM_BOT_TOKEN'),
    ],

```


#### Explanation:

This step registers the Telegram Bot token in Laravel’s service configuration.

It allows Laravel to access the token when sending Telegram notifications.







## STEP 7: Get Chat ID

### Open browser:

```
https://api.telegram.org/botYOUR_TOKEN/getUpdates

```

#### Example:

```
https://api.telegram.org/bot123456789:ABCDEF/getUpdates

```

### Output:

```
{
 "ok": true,
 "result": [
  {
   "message": {
    "chat": {
     "id": 987654321
    }
   }
  }
 ]
}

```

### Your Chat ID:

```
987654321

```


#### Explanation:

The Chat ID identifies the Telegram user or group that will receive notifications.

Laravel uses this Chat ID to send messages to the correct Telegram account.





## STEP 8: Create User Model Notification Route

### Open: app/Models/User.php

#### Add:

```
use Illuminate\Notifications\Notifiable;

```

#### Inside class:

```
use Notifiable;

```

#### Add this function:

```
public function routeNotificationForTelegram()
{
    return "987654321";
}

```

### Full example:

```
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    public function routeNotificationForTelegram()
    {
        return "987654321";
    }
}

```


#### Explanation:

This function tells Laravel where to send Telegram notifications for the User model.

It returns the Chat ID so Laravel knows which Telegram account should receive the message.






## STEP 9: Create Notification 

### Run command:

```
php artisan make:notification TelegramNotification

```

### File created: app/Notifications/TelegramNotification.php

```
<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class TelegramNotification extends Notification
{
    public function via($notifiable)
    {
        return ['telegram'];
    }

    public function toTelegram($notifiable)
    {
        return TelegramMessage::create()
            ->content("Hello User Name!\nThis message sent from Laravel 12 Telegram Notification Package.");
    }
}

```


#### Explanation:

This step creates a Notification class that defines the Telegram message content.

Laravel uses this class to format and send messages through the Telegram channel.





## STEP 10: Create Controller

### Command:

```
php artisan make:controller TelegramController 

```


### app/Http/Controllers/TelegramController.php

```
<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\TelegramNotification;

class TelegramController extends Controller
{
    public function send()
    {
        $user = new User();

        $user->notify(new TelegramNotification());

        return "Telegram notification sent successfully!";
    }
}

```



#### Explanation:

The controller triggers the Telegram notification when a specific route is accessed.

It calls the Notification class and sends the message to the Telegram user.






## STEP 11: Create Route

### Open: routes/web.php

#### Add:

```
use App\Http\Controllers\TelegramController;

Route::get('/telegram/send', [TelegramController::class, 'send']);

```


#### Explanation:

This step creates a URL route that connects the browser request to the controller.

When the route is opened, Laravel executes the controller and sends the notification.






## STEP 12: Run Project

### Command:

```
php artisan serve

```

### Open browser:

```
http://127.0.0.1:8000/telegram/send

```


#### Explanation:

This command starts the Laravel development server.

It allows you to open the project in your browser and test the Telegram notification.





## STEP 13: FINAL OUTPUT

### Browser Output:

```
Telegram notification sent successfully!

```

### Telegram Output:

```
Hello User Name!

This message sent from Laravel 12 Telegram Notification Package.

```


#### Explanation:

When the route is accessed, Laravel sends the notification to Telegram instantly.

You will see a success message in the browser and receive the message in Telegram.






## STEP 14: Advanced Telegram Notification Examples

### app/Notifications/TelegramNotification.php


### Example 1: Simple Text Notification

```
<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class TelegramNotification extends Notification
{
    public function via($notifiable)
    {
        return ['telegram'];
    }

    public function toTelegram($notifiable)
    {
        return TelegramMessage::create()
            ->content("Hello User Name!\nThis message sent from Laravel 12 Telegram Notification Package.");
    }
}

```


#### Then show this type message in your Telegram :


<img width="404" height="100" alt="Screenshot 2026-02-20 144832" src="https://github.com/user-attachments/assets/12699f06-1de1-4ab8-8bc5-1eb36ff966be" />



### Example 2: Telegram Notification with Button

```
<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class TelegramNotification extends Notification
{
    public function via($notifiable)
    {
        return ['telegram'];
    }

    public function toTelegram($notifiable)
    {
        return TelegramMessage::create()
            ->content("Laravel 12 Telegram Notification with Button")
            ->button('Visit Laravel Website', 'https://laravel.com');
    }
}

```


#### Then show this type message in your Telegram :


<img width="407" height="105" alt="Screenshot 2026-02-20 144843" src="https://github.com/user-attachments/assets/49021e6f-cc1f-424b-a984-5ad0151bbf54" />




### Example 3: Telegram Notification with Formatted Text

```
<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class TelegramNotification extends Notification
{
    public function via($notifiable)
    {
        return ['telegram'];
    }

    public function toTelegram($notifiable)
    {
        return TelegramMessage::create()
            ->content("*Laravel 12 Notification*\n\nThis is *bold text*\nThis is _italic text_\n\n[Visit Laravel](https://laravel.com)");
    }
}

```

#### Then show this type message in your Telegram :


<img width="326" height="392" alt="Screenshot 2026-02-20 145542" src="https://github.com/user-attachments/assets/7228f329-c20c-4427-997d-a959fc7a27b2" />




#### Explanation:

These examples show different types of Telegram messages such as simple text, buttons, and formatted text.

This helps you create more interactive and professional Telegram notifications.





---

# Project Folder Structure:

```
PHP_Laravel12_Telegram/
│
├── app/
│   │
│   ├── Http/
│   │   └── Controllers/
│   │       └── TelegramController.php
│   │
│   ├── Models/
│   │   └── User.php
│   │
│   ├── Notifications/
│   │   └── TelegramNotification.php
│   │
│   └── Providers/
│
├── bootstrap/
│   └── app.php
│
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── cache.php
│   ├── database.php
│   ├── filesystems.php
│   ├── logging.php
│   ├── mail.php
│   ├── queue.php
│   ├── services.php     ← Telegram config added here
│   └── session.php
│
├── database/
│   ├── factories/
│   │   └── UserFactory.php
│   │
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   └── 0001_01_01_000002_create_jobs_table.php
│   │
│   └── seeders/
│       └── DatabaseSeeder.php
│
├── public/
│   ├── index.php
│   └── .htaccess
│
├── resources/
│   ├── views/
│   │   └── welcome.blade.php
│   │
│   ├── css/
│   └── js/
│
├── routes/
│   ├── web.php          ← Telegram route added here
│   └── console.php
│
├── storage/
│   ├── app/
│   ├── framework/
│   └── logs/
│
├── tests/
│
├── vendor/
│
├── .env                 ← TELEGRAM_BOT_TOKEN added here
├── .env.example
├── artisan
├── composer.json
├── composer.lock
├── package.json
├── phpunit.xml
├── README.md
└── vite.config.js

```
