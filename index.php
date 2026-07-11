<?php
include "db.php";

$result = mysqli_query($conn,"SELECT * FROM AP1");
?>

<!DOCTYPE html>
<html>
<head>

<title>CRUD Application</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="style.css">

</head>

<body>

<div class="container mt-5">

<h2 class="text-center text-success">
Basic CRUD Application
</h2>

<a href="add.php" class="btn btn-primary mb-3">
Add New Post
</a>

<table class="table table-bordered table-hover">

<tr>

<th>ID</th>
<th>Title</th>
<th>Content</th>
<th>Date</th>
<th>Action</th>

</tr>

<?php

while($row=mysqli_fetch_assoc($result))
{

?>

<tr>

<td><?php echo $row['id']; ?></td>

<td><?php echo $row['title']; ?></td>

<td><?php echo $row['content']; ?></td>

<td><?php echo $row['created_at']; ?></td>

<td>

<a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>

<a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm">Delete</a>

</td>

</tr>

<?php

}

?>

</table>

</div>

</body>
</html>