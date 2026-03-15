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
        $method = $_SERVER['REQUEST_METHOD'];
        $statusColor = $statusCode == 200 ? '#4ade80' : '#f87171'; // Tailwind-like Green/Red
        $statusText = [
            200 => 'OK',
            201 => 'Created',
            404 => 'Not Found',
            500 => 'Internal Error'
        ][$statusCode] ?? 'Unknown';

        // Extracting matched route details safely
        $matchedRoute = $this->router->matchedRoute ?? [];
        $url = $matchedRoute['url'] ?? 'N/A';
        $route = $matchedRoute['route'] ?? 'N/A';
        $controller = $matchedRoute['controller'] ?? 'Unknown';
        $controllerMethod = $matchedRoute['method'] ?? 'Unknown';

        echo '
        <style>
            :root {
                --debug-blue: #3b82f6;
                --debug-bg: rgba(15, 23, 42, 0.85);
                --debug-text: #f1f5f9;
                --debug-border: rgba(59, 130, 246, 0.3);
            }
            #debug-fab {
                position: fixed;
                bottom: 20px;
                right: 20px;
                width: 50px;
                height: 50px;
                background: var(--debug-blue);
                color: white;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                box-shadow: 0 4px 15px rgba(0,0,0,0.3);
                z-index: 10001;
                transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                font-weight: bold;
            }
            #debug-fab:hover { transform: scale(1.1) rotate(10deg); }
            
            #debug-panel {
                position: fixed;
                bottom: 80px;
                right: 20px;
                width: 450px;
                max-width: calc(100vw - 40px);
                max-height: 500px;
                background: var(--debug-bg);
                backdrop-filter: blur(12px);
                border: 1px solid var(--debug-border);
                border-radius: 16px;
                color: var(--debug-text);
                font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                z-index: 10000;
                overflow: hidden;
                display: none;
                flex-direction: column;
                box-shadow: 0 10px 40px rgba(0,0,0,0.5);
                animation: debugSlideIn 0.3s ease-out;
            }
            @keyframes debugSlideIn {
                from { opacity: 0; transform: translateY(20px) scale(0.95); }
                to { opacity: 1; transform: translateY(0) scale(1); }
            }
            
            .debug-header {
                padding: 16px;
                background: rgba(59, 130, 246, 0.1);
                border-bottom: 1px solid var(--debug-border);
                display: flex;
                flex-direction: column;
                gap: 8px;
            }
            .debug-status-pill {
                display: inline-flex;
                align-items: center;
                padding: 2px 8px;
                border-radius: 6px;
                font-size: 12px;
                font-weight: 600;
                background: rgba(255,255,255,0.1);
            }
            .debug-scrollable {
                flex: 1;
                overflow-y: auto;
                padding: 16px;
                font-size: 13px;
            }
            .debug-section { margin-bottom: 20px; }
            .debug-section-title {
                color: var(--debug-blue);
                font-size: 11px;
                text-transform: uppercase;
                letter-spacing: 0.1em;
                font-weight: 700;
                margin-bottom: 8px;
                display: flex;
                align-items: center;
                gap: 6px;
            }
            .debug-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 8px;
            }
            .debug-card {
                background: rgba(255,255,255,0.05);
                padding: 10px;
                border-radius: 8px;
                border: 1px solid rgba(255,255,255,0.05);
            }
            .debug-card label { display: block; font-size: 10px; opacity: 0.6; margin-bottom: 2px; }
            .debug-card value { display: block; font-weight: 600; color: #fff; }
            
            .debug-table { width: 100%; border-collapse: collapse; margin-top: 8px; }
            .debug-table th { text-align: left; opacity: 0.5; font-size: 11px; padding: 4px; border-bottom: 1px solid rgba(255,255,255,0.1); }
            .debug-table td { padding: 6px 4px; border-bottom: 1px solid rgba(255,255,255,0.05); font-family: monospace; font-size: 12px; }
            
            .debug-footer {
                padding: 8px 16px;
                background: rgba(0,0,0,0.2);
                border-top: 1px solid var(--debug-border);
                font-size: 11px;
                display: flex;
                justify-content: space-between;
                opacity: 0.7;
            }
            details summary { cursor: pointer; padding: 4px 0; outline: none; transition: color 0.2s; }
            details summary:hover { color: var(--debug-blue); }
        </style>

        <div id="debug-fab" onclick="toggleDebug()" style="background: ' . ($statusCode >= 400 ? '#ef4444' : 'var(--debug-blue)') . ';">
            <span style="font-size: 14px; font-family: monospace;">' . $statusCode . '</span>
            ' . ($statusCode >= 400 ? '<div style="position:absolute; top:-4px; right:-4px; width:12px; height:12px; background:#fff; border-radius:50%; border:2px solid #ef4444; animation: pulse 1.5s infinite;"></div>' : '') . '
        </div>

        <style>
            @keyframes pulse {
                0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
                70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
                100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
            }
        </style>

        <div id="debug-panel">
            <div class="debug-header">
                <div style="display:flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 700; font-size: 16px;">Runtime Debugger</span>
                    <span class="debug-status-pill" style="color: ' . $statusColor . ';">' . $statusCode . ' ' . $statusText . '</span>
                </div>
                <div style="font-size: 12px; opacity: 0.8;">
                    <span style="color: var(--debug-blue); font-weight: 700;">' . $method . '</span> 
                    <span style="margin-left: 4px;">' . htmlspecialchars($url) . '</span>
                </div>
            </div>

            <div class="debug-scrollable">
                <div class="debug-section">
                    <div class="debug-grid">
                        <div class="debug-card">
                            <label>Execution Time</label>
                            <value>' . number_format($this->getExecutionTime(), 4) . 's</value>
                        </div>
                        <div class="debug-card">
                            <label>Memory Peak</label>
                            <value>' . $this->formatMemory($this->getPeakMemoryUsage()) . '</value>
                        </div>
                    </div>
                </div>

                <div class="debug-section">
                    <div class="debug-section-title">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        Routing Details
                    </div>
                    <div class="debug-card">
                        <table class="debug-table">
                            <tr><th>Pattern</th><td>' . htmlspecialchars($route) . '</td></tr>
                            <tr><th>Controller</th><td>' . htmlspecialchars($controller) . '</td></tr>
                            <tr><th>Method</th><td>' . htmlspecialchars($controllerMethod) . '()</td></tr>
                        </table>
                    </div>
                </div>

                <div class="debug-section">
                    <div class="debug-section-title">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>
                        Database Queries (' . count(\Core\Database::getQueries()) . ')
                    </div>
                    ' . $this->getDatabaseQueriesHtml() . '
                </div>

                <div class="debug-section">
                    <details>
                        <summary class="debug-section-title" style="margin-bottom:0">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 10h-1.26A8 8 0 1 0 9 20h9a5 5 0 0 0 0-10z"></path></svg>
                            Environment Constants
                        </summary>
                        <div style="margin-top:8px; font-size:11px; padding:8px; background:rgba(0,0,0,0.2); border-radius:8px; max-height:150px; overflow:auto;">
                            ' . $this->getConstantsHtml() . '
                        </div>
                    </details>
                </div>
            </div>

            <div class="debug-footer">
                <span>simple-mvc v1.0</span>
                <span>PHP ' . PHP_VERSION . '</span>
            </div>
        </div>

        <script>
            function toggleDebug() {
                var panel = document.getElementById("debug-panel");
                if (panel.style.display === "none" || panel.style.display === "") {
                    panel.style.display = "flex";
                } else {
                    panel.style.display = "none";
                }
            }
        </script>';
    }

    protected function getDatabaseQueriesHtml()
    {
        $queries = \Core\Database::getQueries();
        if (empty($queries)) return "<div style='opacity:0.5'>No queries executed.</div>";

        $html = '<div style="display:flex; flex-direction:column; gap:6px;">';
        foreach ($queries as $index => $query) {
            $html .= '<div style="background:rgba(255,255,255,0.03); padding:8px; border-radius:6px; border-left: 2px solid var(--debug-blue);">';
            $html .= '<div style="font-family:monospace; color:#fff; word-break:break-all;">' . htmlspecialchars($query['sql']) . '</div>';
            if (!empty($query['params'])) {
                $html .= '<div style="font-size:10px; opacity:0.6; margin-top:4px;">Params: ' . htmlspecialchars(json_encode($query['params'])) . '</div>';
            }
            $html .= '<div style="font-size:10px; color:var(--debug-blue); margin-top:2px;">Time: ' . $query['time'] . 'ms</div>';
            $html .= '</div>';
        }
        $html .= '</div>';
        return $html;
    }

    protected function getConstantsHtml()
    {
        $html = '<div style="margin-bottom: 12px;">';
        $html .= '<div style="color:var(--debug-blue); font-size:10px; font-weight:700; margin-bottom:4px;">ENVIRONMENT ($_ENV)</div>';
        $html .= '<table style="width:100%; font-size:11px;">';
        $sensitiveKeys = ['password', 'user', 'database', 'secret', 'key', 'token', 'auth', 'host'];
        foreach ($_ENV as $key => $value) {
            $isSensitive = false;
            foreach ($sensitiveKeys as $sKey) {
                if (strpos(strtolower($key), $sKey) !== false) {
                    $isSensitive = true;
                    break;
                }
            }
            if ($isSensitive) continue;

            $valStr = is_string($value) ? $value : json_encode($value);
            $html .= "<tr><td style='opacity:0.6; width:40%; padding:2px 0;'>{$key}</td><td style='color:#fff; word-break:break-all;'>".htmlspecialchars($valStr)."</td></tr>";
        }
        $html .= '</table></div>';

        $constants = get_defined_constants(true)['user'] ?? [];
        if (!empty($constants)) {
            $html .= '<div>';
            $html .= '<div style="color:var(--debug-blue); font-size:10px; font-weight:700; margin-bottom:4px;">USER CONSTANTS</div>';
            $html .= '<table style="width:100%; font-size:11px;">';
            foreach ($constants as $key => $value) {
                if (is_array($value) || is_object($value)) continue;
                $html .= "<tr><td style='opacity:0.6; width:40%; padding:2px 0;'>{$key}</td><td style='color:#fff; word-break:break-all;'>".htmlspecialchars((string)$value)."</td></tr>";
            }
            $html .= '</table></div>';
        }
        
        return $html;
    }

    protected function renderRouteInfo() {}
    protected function renderDatabaseQueries() {}


}