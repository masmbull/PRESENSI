<?php // shot.php — render sebuah view + screenshot headless (desktop & HP).
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$view = $argv[1] ?? 'wajah';
$w = (int) ($argv[2] ?? 1440);
$h = (int) ($argv[3] ?? 1900);

$data = [];
if ($view === 'wajah') {
    $data['stats'] = [
        'cities' => App\Models\City::count(),
        'stores' => App\Models\Store::count(),
        'employees' => App\Models\Employee::count(),
        'linked' => App\Models\Employee::whereNotNull('face_key')->count(),
        'no_face' => App\Models\Employee::whereNull('face_key')->count(),
    ];
}
if ($view === 'admin.dashboard') {
    $req = Illuminate\Http\Request::create('/kelola-wajah', 'GET');
    $req->attributes->set('admin_user', App\Models\User::first());
    $app->instance('request', $req);
    $ctrl = new App\Http\Controllers\Admin\AttendanceAdminController();
    $resp = $ctrl->dashboard();
    $html = $resp->render();
} else {
    $html = view($view, $data)->render();
}

$file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "shot-$view.html";
file_put_contents($file, $html);

$edge = 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe';
$shots = realpath(__DIR__ . '/../storage/app');
$png = $shots . DIRECTORY_SEPARATOR . "shot-$view-$w.png";
$userData = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'edge-shot-profile-' . getmypid();

$cmd = escapeshellarg($edge) . ' --headless=new --disable-gpu --hide-scrollbars --no-first-run'
    . ' --no-default-browser-check --user-data-dir=' . escapeshellarg($userData)
    . " --window-size=$w,$h --virtual-time-budget=5000 --screenshot=" . escapeshellarg($png)
    . ' ' . escapeshellarg('file:///' . str_replace('\\', '/', $file)) . ' 2>NUL';
shell_exec($cmd);

echo 'view : ' . $view . PHP_EOL;
echo 'html : ' . $file . ' (' . strlen($html) . ' byte)' . PHP_EOL;
echo 'png  : ' . $png . (is_file($png) ? ' (' . filesize($png) . ' byte, ' . $w . 'x' . $h . ')' : ' TIDAK ADA') . PHP_EOL;