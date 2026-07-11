<?php

include "db.php";

$id=$_GET['id'];

$data=mysqli_query($conn,"SELECT * FROM AP1 WHERE id=$id");

$row=mysqli_fetch_assoc($data);

if(isset($_POST['update']))
{

$title=$_POST['title'];

$content=$_POST['content'];

mysqli_query($conn,"UPDATE AP1 SET

title='$title',

content='$content'

WHERE id=$id");

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

<h3>Edit Record</h3>

<form method="post">

<input type="text"

name="title"

class="form-control mb-3"

value="<?php echo $row['title']; ?>">

<textarea

name="content"

class="form-control mb-3"><?php echo $row['content']; ?></textarea>

<button class="btn btn-warning"

name="update">

Update

</button>

</form>

</div>

</body>

</html>