<?php

include "db.php";

if(isset($_POST['save']))
{

$title=$_POST['title'];
$content=$_POST['content'];

mysqli_query($conn,"INSERT INTO AP1(title,content)
VALUES('$title','$content')");

header("Location:index.php");

}

?>

<!DOCTYPE html>

<html>

<head>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<div class="container mt-5">

<h3>Add New Record</h3>

<form method="post">

<input type="text" name="title" class="form-control mb-3" placeholder="Title" required>

<textarea name="content" class="form-control mb-3" placeholder="Content"></textarea>

<button class="btn btn-success" name="save">
Save
</button>

</form>

</div>

</body>

</html>