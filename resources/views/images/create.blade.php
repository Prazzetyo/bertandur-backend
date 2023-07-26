<html lang="en">
<head>
    <title>My Image</title>
    <link rel="stylesheet" href="">
</head>
<body>
    <form action="testimage" method="post" enctype="multipart/form-data">
        @csrf
        <input type="file" name="image" id="image">
        <button type="submit">Submit</button>
    </form>
</body>
</html>