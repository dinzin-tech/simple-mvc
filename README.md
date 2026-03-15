# Simple MVC Framework

A lightweight, high-performance MVC framework built in PHP. It is designed for internal projects, providing essential features like routing, database modeling, querying, and templating.

---

## 🚀 Installation & Setup

1. **Clone the repository** and install dependencies:
   ```bash
   composer install
   ```

2. **Environment Variables**:
   Create a `.env` file in the root directory (you can copy from `.env.example` if it exists).
   Define your default database connection:
   ```env
   DEFAULT_DB_HOST=127.0.0.1
   DEFAULT_DB_DATABASE=my_database
   DEFAULT_DB_USER=root
   DEFAULT_DB_PASSWORD=secret
   ```

3. **Running the Dev Server**:
   You can serve the application directly using PHP's built-in web server:
   ```bash
   composer serve
   # or
   php -S localhost:8000 -t public/
   ```

---

## 🚦 Routing

Routes are primarily defined and registered via controller annotations, or manually in the `Router`. The application automatically scans controllers to look for route definitions.

### Example: Controller Annotation

```php
namespace App\Controllers;

use Core\Controller;
use Core\Http\Request;
use Core\Http\Response;

class HomeController extends Controller {
    
    /**
     * @Route(path="/", methods="GET", name="home")
     */
    public function index(Request $request): Response {
        // Render a Twig view view 
        return $this->render('home/index.twig', ['title' => 'Welcome']);
    }
}
```

---

## 🗄 Models & Database

Models interact with the database tables. By default, a model `User` will look for a table named `users`.

### Basic Usage

```php
namespace App\Models;

use Core\Model;

class User extends Model {
    protected string $table = 'users';
    
    public int $id;
    public string $name;
    public string $email;
}
```

```php
// Finding a record by primary key
$user = User::find(1);

// Finding an array of records by criteria
$users = User::findBy(['status' => 'active'], limit: 10, orderBy: 'created_at DESC');

// Saving a record
$user = new User();
$user->name = 'John Doe';
$user->save();
```

---

## 🏗 Query Builder

For more complex queries, the framework comes with a fluent Query Builder, accessible directly from your models.

```php
// Select specific columns with Where clauses
$activeUsers = User::query()
    ->select('id', 'name')
    ->where('status', 'active')
    ->orWhere('role', 'admin')
    ->orderBy('created_at', 'DESC')
    ->limit(5)
    ->get(); // returns an array of records

// Get a single record
$firstUser = User::query()->where('id', 1)->first();

### Complex Queries (JOINs)

You can perform various joins (`INNER`, `LEFT`, `RIGHT`) using the Query Builder:

```php
$postsWithAuthors = Post::query()
    ->select('posts.id', 'posts.title', 'users.name')
    ->join('users', 'posts.user_id', '=', 'users.id')
    ->where('posts.published', 1)
    ->get();

// Left join example
$usersWithProfiles = User::query()
    ->leftJoin('profiles', 'users.id', '=', 'profiles.user_id')
    ->get();
```

### Raw SQL Queries

If you need to execute complex or raw SQL queries directly, use the `raw()` method:

```php
$stats = User::query()
    ->raw('SELECT role, COUNT(id) as total FROM users WHERE status = :status GROUP BY role', ['status' => 'active'])
    ->get();
```

### Inserts, Updates, Deletes

```php
// Insert data
User::query()->insert([
    'name' => 'Alice',
    'email' => 'alice@example.com'
]);

// Update data
User::query()
    ->where('id', 1)
    ->update(['status' => 'inactive']);

// Delete data
User::query()
    ->where('id', 5)
    ->delete();
```

---

## 🚥 Controllers & Views

Controllers extend `Core\Controller` which provides helpful methods like `render()` and `redirect()`.
The framework integrates **Twig** natively for views.

```php
public function dashboard(): Response {
    $stats = [...];
    return $this->render('admin/dashboard.twig', ['stats' => $stats]);
}

public function logout(): Response {
    return $this->redirect('/login');
}
```

---

## 🧪 Testing

The framework uses **PHPUnit** for automated testing.

### Running Tests

```bash
composer test
# or
./vendor/bin/phpunit
```

### Writing Tests

Create test files inside the `tests/` directory extending `PHPUnit\Framework\TestCase`. The bootstrap configuration securely loads environment variables and Composer tools for you over test runs.

```php
namespace Tests;

use PHPUnit\Framework\TestCase;

class SimpleTest extends TestCase {
    public function testExample() {
        $this->assertTrue(true);
    }
}
```
