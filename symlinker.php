<?php

session_start();

// configuration section starts

$password = '';
$default_path = '/';

// configuration section ends

?>

<html>
<head>
<title>Symlinker</title>
</head>
<style type="text/css">
a:link, a:visited, a:active {
    color: blue;
}
</style>
<body onload="document.getElementById('focused').focus();">

<?php

$version = '0.0.2';
$error = false;

function is_authenticated()
{
    global $password;

    if (isset($_GET['operation']) && $_GET['operation'] == 'logout') {
        unset($_SESSION['submitted_password']);
    } else if (isset($_POST['operation']) && $_POST['operation'] == 'login') {
        $_SESSION['submitted_password'] = md5($_POST['submitted_password']);
    }

    return isset($_SESSION['submitted_password']) &&
           $_SESSION['submitted_password'] == md5($password);
}

function get_login_screen()
{
    global $password;

    print '<div style="text-align:center;">
           <h1>Symlinker login</h1>';

    if (isset($_SESSION['submitted_password'])) {
        print 'Invalid password.  Try again!<br><br>';
    }

    if (!isset($password) || $password == '') {
        print 'You have to set the $password variable in order to use Symlinker.<br>';
    } else {
        print '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">
              Password: <input type="password" name="submitted_password" id="focused">
              <input type="submit" name="operation" value="login">
              </form>';
    }

    print '</div>';
}

function symlink_error($source, $destination)
{
    global $error;
    print "<b>failed to create symlink: $source &rArr; $destination</b><br>";
    $error = true;
}

function process_operation($path, $post)
{
    global $error;

    if (!isset($post['operation'])) {
        return;
    }

    $operation = $post['operation'];

    switch ($operation) {

    case 'symlink':
        $source = $post['source'];
        $destination = $post['destination'];

        if ($source != '/') {
            $source = "$path/$source";
        }

        if (@symlink($destination, $source) === false)
        {
            symlink_error($source, $destination);
        }

    case 'update':
        for ($i=0; isset($post["source_$i"]) && isset($post["destination_$i"]); $i++) {
            $source = $post["source_$i"];
            $destination = $post["destination_$i"];

            if (@readlink($source) == $destination) {
                continue;
            }

            if (file_exists($source) && !is_link($source)) {
                symlink_error($source, $destination);
                continue;
            }

            if (@unlink($source) === false) {
                symlink_error($source, $destination);
                continue;
            }

            if (@symlink($destination, $source) === false) {
                symlink_error($source, $destination);
            }
        }

    }

    if ($error) {
        print '<br>';
    }
}

function get_navigation_bar($path)
{
    $dirs = explode('/', $path);
    array_shift($dirs);
    $out = '<div style="float:left">';
    $out .= '<a href="' . $_SERVER['PHP_SELF']. '?path=/">[root]</a> ';

    if ($path == '/') {
        return $out;
    }

    $path = '';
    foreach ($dirs as $dir) {
        $path .= "/$dir";
        $out .= '/ <a href="' . $_SERVER['PHP_SELF'] . "?path=$path\">$dir</a> ";
    }

    $out .= '</div><div style="float:right">' .
            '<a href="' . $_SERVER['PHP_SELF'] . '?operation=logout">logout</a>' .
            '</div><br>';

    return $out;
}

function get_symlink_creator($path)
{
    print '<form method="post" action="' . $_SERVER['PHP_SELF'] . "?path=$path\">" .
          '<br>' .
          '<input type="text" name="source" id="focused"> &rArr; ' .
          '<input type="text" name="destination"> ' .
          '<input type="submit" name="operation" value="symlink">' .
          '<br>' .
          '</form>';
}

function get_filelist($path)
{
    global $default_path;

    print '<form method="post" action="' . $_SERVER['PHP_SELF'] . "?path=$path\">";
    $files = @scandir($path);

    if ($files === false) {
        print "failed to list this directory: $path<br>" .
              "you may want to visit the default directory: " .
              "<a href=\"" . $_SERVER['PHP_SELF'] . "?path=$default_path\">$default_path</a><br>";
        return;
    }

    $dirs = $nondirs = array();
    $id = 0;

    foreach ($files as $file) {

        if ($file == '.' || $file == '..') {
            continue;
        }

        $filepath = "$path/$file";
        $escaped_filepath = str_replace('"', '\"', $filepath);

        $filename = htmlspecialchars($file);
        if (is_dir($filepath)) {
            $filename = "<a href=\"" . $_SERVER['PHP_SELF'] . "?path=$escaped_filepath\">$filename</a>";
        }
        if (is_link($filepath)) {
            $target = readlink($filepath);
            $filename = "<input type=\"hidden\" name=\"source_$id\" value=\"$escaped_filepath\">" .
                        "$filename &rArr; ".
                        "<input type=\"text\" name=\"destination_$id\" value=\"$target\">";
            $id++;

            if (@stat($filepath) === false) {
                $filename = "$filename <span style=\"color:red; font-weight:bold;\">" .
                            "DANGLING LINK!</span>";
            }
        }

        if (is_dir($filepath)) {
            array_push($dirs, $filename);
        } else {
            array_push($nondirs, $filename);
        }
    }

    print $path == '/' ? '' : "<a href=\"" . $_SERVER['PHP_SELF'] . "?path=$path/..\">..</a><br>\n";
    foreach (array($dirs, $nondirs) as $filenames) {
        foreach ($filenames as $filename) {
            print "$filename<br>\n";
        }
    }

    print '<br><input type="submit" name="operation" value="update">';
    print '</form>';
}

function get_footer($version)
{
    print '<hr><div style="text-align:center;"><a href="https://github.com/mondalaci/symlinker">' .
          "Symlinker</a> $version by " .
          '<a href="http://laci.monda.hu">L&aacute;szl&oacute; Monda</a>, ' .
          'licensed under the <a href="http://www.gnu.org/licenses/gpl-3.0.html">' .
          'GPLv3</a></div>';
}

error_reporting(E_ALL);

$path = isset($_GET['path']) ? realpath($_GET['path']) : $default_path;

if (!is_authenticated()) {
    print get_login_screen();
} else {
    process_operation($path, $_POST);
    print get_navigation_bar($path);
    print get_symlink_creator($path);
    print get_filelist($path);
}

print get_footer($version);

?>

</body>
</html>
