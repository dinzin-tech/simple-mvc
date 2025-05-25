<?php

namespace Core;

use Core\Database;
use Core\Router;

class Debug
{
    private $queries = [];
    private $startTime;
    private $router;

    public function __construct(Router $router)
    {
        // Start timing when the Debug class is instantiated
        $this->startTime = microtime(true);
        $this->router = $router;
    }

    public function logQuery($query)
    {
        $this->queries[] = $query;
    }

    public function getExecutionTime()
    {
        return microtime(true) - $this->startTime;
    }

    public function getMemoryUsage()
    {
        return memory_get_usage();
    }

    public function getPeakMemoryUsage()
    {
        return memory_get_peak_usage();
    }

    private function formatMemory($memory)
    {
        // Convert memory size to a human-readable format
        if ($memory < 1024) {
            return $memory . ' bytes';
        } elseif ($memory < 1048576) {
            return round($memory / 1024, 2) . ' KB';
        } else {
            return round($memory / 1048576, 2) . ' MB';
        }
    }

    /*public function render()
    {
        // Only render if debugging is enabled in the .env file
        if ($_ENV['DEBUG_MODE'] != 'true') {
            return;
        }

        echo '<br />';
        echo '<details open >';
        echo '<summary>Debug Info</summary>';
        echo '<div class="debug-info" style="background-color: #f9f9f9; border: 1px solid #ccc; padding: 10px; margin-top: 10px;">';
        echo '<h4>Debug Information</h4>';
                
        echo '<strong>Executed Queries:</strong><br>';
        echo '<ul>';
        foreach ($this->queries as $query) {
            // echo '<li>' . htmlspecialchars($query) . '</li>';
            echo '<li>' . $query . '</li>';
        }
        echo '</ul>';
       
        echo '<strong>Execution Time:</strong> ' . number_format($this->getExecutionTime(), 4) . ' seconds<br>';
        echo '<strong>Memory Usage:</strong> ' . $this->formatMemory($this->getMemoryUsage()) . '<br>';
        echo '<strong>Peak Memory Usage:</strong> ' . $this->formatMemory($this->getPeakMemoryUsage()) . '<br>';
        echo '<strong>Included Files:</strong><br>';
        echo '<ul>';

        // foreach (get_included_files() as $file) {
        //     echo '<li>' . $file . '</li>';
        // }

        foreach (get_included_files() as $file) {
            if (strpos($file, 'vendor') === false) {
                echo '<li>' . $file . '</li>';
            }
        }

        // show all defined constants
        
        echo '<details >
            <summary><strong>Defined Constants:</strong></summary>';
        echo '<ul>';
        $defined_constants = get_defined_constants();
        $defined_constants = array_reverse($defined_constants, true);
        foreach ($defined_constants as $key => $value) {
            echo '<li>' . $key . ' => ' . $value . '</li>';
        }
        echo '</ul>';
        echo '</details>';

        echo '</ul>';

        echo '</ul>';
        echo '</div>';

        echo '<div style="background: #fff; padding: 20px; margin: 20px; border: 1px solid #ccc;">';
        echo '<h3>Debug Information</h3>';
        
        $this->renderRouteInfo();
        $this->renderDatabaseQueries();
        // Add other debug sections
        
        echo '</div>
        </details>';
    }*/

    public function render()
    {
        // Only render if debugging is enabled in the .env file
        if ($_ENV['DEBUG_MODE'] != 'true') {
            return;
        }

        $statusCode = http_response_code();
        // get the HTTP method
        $method = $_SERVER['REQUEST_METHOD'];

        // If 404, display an error message
        $statusColor = $statusCode == 200 ? '#4CAF50' : '#FF0000';
        $statusText = $statusCode == 404 ? 'Not Found' : 'OK';

        // Extracting matched route details safely
        $matchedRoute = $this->router->matchedRoute ?? [];
        $url = $matchedRoute['url'] ?? 'N/A';
        $route = $matchedRoute['route'] ?? 'N/A';
        $controller = $matchedRoute['controller'] ?? 'Unknown Controller';
        $controllerMethod = $matchedRoute['method'] ?? 'Unknown Method';

        echo '<div id="debug-panel" style="
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background-color: #222;
            color: #fff;
            font-family: Arial, sans-serif;
            padding: 1px;
            border-top: 3px solid #ff9800;
            z-index: 9999;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.3);
        ">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <strong>Status:</strong> <span style="color: ' . $statusColor . ';">' . $statusCode . ' (' . $statusText . ')</span> |
                    <strong>'.$method.' </strong> <span style="color: #2196F3;">' . htmlspecialchars($url) . '</span> |
                    <strong>Route:</strong> <span style="color: #4CAF50;">' . htmlspecialchars($route) . '</span> |
                    <strong>Controller:</strong> <span style="color: #FFEB3B;">' . htmlspecialchars($controller) . '</span> |
                    <strong>Method:</strong> <span style="color: #FFEB3B;">' . htmlspecialchars($controllerMethodmethod) . '</span>
                </div>
                <button onclick="toggleDebug()" style="
                    background: #ff9800;
                    color: #fff;
                    border: none;
                    padding: 5px 10px;
                    cursor: pointer;
                    font-weight: bold;
                    border-radius: 5px;
                ">Toggle Debug</button>
            </div>

            <div id="debug-content" style="
                display: none;
                max-height: 300px;
                overflow-y: auto;
                padding: 10px;
                margin-top: 10px;
                background: #333;
                border-radius: 5px;
            ">
                <h4 style="margin: 0 0 10px; color: #ff9800;">Debug Information</h4>
                <strong>Executed Queries:</strong>
                <ul style="list-style: none; padding-left: 0;">';

                foreach ($this->queries as $query) {
                    echo '<li style="background: #444; padding: 5px; margin: 2px 0; border-radius: 5px;">' . htmlspecialchars($query) . '</li>';
                }

        echo '  </ul>
                <strong>Execution Time:</strong> ' . number_format($this->getExecutionTime(), 4) . ' seconds<br>
                <strong>Memory Usage:</strong> ' . $this->formatMemory($this->getMemoryUsage()) . '<br>
                <strong>Peak Memory Usage:</strong> ' . $this->formatMemory($this->getPeakMemoryUsage()) . '<br>
                
                <br><strong>Included Files:</strong>
                <ul style="list-style: none; padding-left: 0;">';

                foreach (get_included_files() as $file) {
                    if (strpos($file, 'vendor') === false) {
                        echo '<li style="background: #444; padding: 5px; margin: 2px 0; border-radius: 5px;">' . htmlspecialchars($file) . '</li>';
                    }
                }

        echo '  </ul>

                <details>
                    <summary style="cursor: pointer; font-weight: bold; color: #ff9800;">Defined Constants</summary>
                    <div style="max-height: 150px; overflow-y: auto; background: #444; padding: 5px; border-radius: 5px;">
                        <ul style="padding: 5px;">';

                        $defined_constants = get_defined_constants();
                        $defined_constants = array_reverse($defined_constants, true);
                        foreach ($defined_constants as $key => $value) {
                            echo '<li style="color: #ccc;">' . htmlspecialchars($key) . ' => ' . htmlspecialchars($value) . '</li>';
                        }

        echo '          </ul>
                    </div>
                </details>

                <hr style="border-color: #555;">
                <h4 style="color: #ff9800;">Route Information</h4>';
                $this->renderRouteInfo();

        echo '  <h4 style="color: #ff9800;">Database Queries</h4>';
                $this->renderDatabaseQueries();

        echo '  </div>
        </div>

        <script>
            function toggleDebug() {
                var content = document.getElementById("debug-content");
                content.style.display = (content.style.display === "none") ? "block" : "none";
            }
        </script>';
    }

    protected function renderRouteInfo()
    {
        echo '
        <details open>
            <summary>Route Information</summary>
            <table border="1" cellpadding="5">';
        echo '<tr><th>Matched URL</th><th>Route Pattern</th><th>Controller</th><th>Method</th></tr>';
        
        if (!empty($this->router->matchedRoute)) {
            echo '<tr>';
            echo '<td>'.$this->router->matchedRoute['url'].'</td>';
            echo '<td>'.$this->router->matchedRoute['route'].'</td>';
            echo '<td>'.$this->router->matchedRoute['controller'].'</td>';
            echo '<td>'.$this->router->matchedRoute['method'].'()</td>';
            echo '</tr>';
        } else {
            echo '<tr><td colspan="4">No route matched</td></tr>';
        }
        
        echo '</table>
        </details>';
    }

    protected function renderDatabaseQueries()
    {
        $queries = Database::getQueries();
        
        echo '<h4>Database Queries ('.count($queries).')</h4>';
        echo '<table border="1" cellpadding="5">';
        echo '<tr><th>#</th><th>Query</th><th>Params</th><th>Time (ms)</th></tr>';
        
        foreach ($queries as $index => $query) {
            echo '<tr>';
            echo '<td>'.($index + 1).'</td>';
            echo '<td>'.htmlspecialchars($query['sql']).'</td>';
            echo '<td>'.print_r($query['params'], true).'</td>';
            echo '<td>'.$query['time'].'</td>';
            echo '</tr>';
        }
        
        echo '</table>';
    }

}