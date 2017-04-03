<?php

if ($_POST) {
    $root = realpath("../../") . DIRECTORY_SEPARATOR;

    $configFile = fopen($root . "config.php", "w+") or die("Dosya yaratilamadi.");

    $root = trim($_POST["root"]);
    $postPerPage = trim($_POST["postPerPage"]);
    $adminDirectory = trim($_POST["adminDirectory"]);
    $host = trim($_POST["host"]);
    $database = trim($_POST["database"]);
    $user = trim($_POST["username"]);
    $pass = trim($_POST["password"]);

    $code = '<?php $config = [
        \'root\' => \'' . $root . '\',
        \'postPerPage\' => ' . $postPerPage . ',
        \'adminDirectory\' => \'' . $adminDirectory . '\',
        \'database\' => [
            \'dsn\' => \'mysql:host='.$host.';dbname=' . $database . ';charset=utf8\',
            \'usr\' => \'' . $user . '\',
            \'pwd\' => \'' . $pass . '\'
        ]
    ];';

    fwrite($configFile, $code);
    fclose($configFile);

    $mysqli = new mysqli("localhost", $user, $pass, $database);
    if (mysqli_connect_errno()) {
        printf("Veritabanina baglanamadi: %s\n", mysqli_connect_error());
        exit();
    }

    $sqlSource = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'database.sql');

    if (mysqli_multi_query($mysqli, $sqlSource)) {
        unlink(__DIR__ . DIRECTORY_SEPARATOR . "index.php");
        unlink(__DIR__ . DIRECTORY_SEPARATOR . "database.sql");
        rmdir(__DIR__);
    }
} else { ?>

    <style type="text/css">
        input {
            width: 100%;
        }
    </style>

    <p>[!] Kurulum sonrası install klasörü otomatik olarak silinecek. </p>
    <p>[!] Install klasörü içindeki database.sql otomatik olarak veritabanına import edilecek. </p>
    <p>[!] Hatalı kurulum yaptığınızı düşünürseniz kök dizininden config.php içini elle düzeltin ve sql i elle import edin. </p>
    <p>[!] Elle kurulum yapmak istemiyorsanız kurulum dosyasını tekrar ftp ye atıp tekrar bu sayfaya gelin. </p>

    <form method="post">
        <div class="field">
            <label class="label">Kök Site Adresi ( / ile bitmeli )</label>
            <p class="control">
                <input class="input" type="text" name="root" placeholder="http://domain.org/blog/ ya da http://domain.org/">
            </p>
        </div>


        <div class="field">
            <label class="label">Sayfa başına düşecek post sayısı</label>
            <p class="control">
                <input class="input" type="number" name="postPerPage" value="7">
            </p>
        </div>


        <div class="field">
            <label class="label">Panel klasörünün adı</label>
            <p class="control">
                <input class="input" type="text" name="adminDirectory" value="admin">
            </p>
        </div>

        <div class="field">
            <label class="label">Database Host</label>
            <p class="control">
                <input class="input" type="text" name="host" value="localhost">
            </p>
        </div>

        <div class="field">
            <label class="label">Database Adı</label>
            <p class="control">
                <input class="input" type="text" name="database">
            </p>
        </div>

        <div class="field">
            <label class="label">Database Kullanıcı Adı</label>
            <p class="control">
                <input class="input" type="text" name="username">
            </p>
        </div>

        <div class="field">
            <label class="label">Database Şifre</label>
            <p class="control">
                <input class="input" type="text" name="password">
            </p>
        </div>
        <br><br>
        <input type="submit" value="Kur!">
    </form>
<?php }

