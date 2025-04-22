<?php
session_start();

if (isset($_POST['submit'])) {
    include '../include/env.php';

    $id = $_POST['id'];
    $food_name = $_POST['food_name'];
    $food_des = $_POST['food_des'];
    $food_price = $_POST['food_price'];
    $food_discount = $_POST['food_discount'];
    $catagory_id = $_POST['catagory_id'];
    $old_image = $_POST['image_name']; // old image name
    $food_img = $_FILES['food_img'];

    // Check if image is updated or not
    if ($food_img['size'] == 0) {
        // No new image
        $update = "UPDATE foods SET 
                    food_name = '$food_name', 
                    food_des = '$food_des', 
                    food_price = '$food_price', 
                    food_discount = '$food_discount', 
                    catagory_id = '$catagory_id' 
                   WHERE id = $id";

        $result = mysqli_query($conn, $update);

        if (!$result) {
            die("Query Failed: " . mysqli_error($conn));
        }

        $_SESSION['success'] = "Food Updated Successfully!";
        header("Location: ../backend_files/all_foods.php");
        exit();
    } else {
        // New image uploaded
        if (file_exists("../backend_files/uploads/foods/$old_image")) {
            unlink("../backend_files/uploads/foods/$old_image");
        }

        $extension = pathinfo($food_img['name'], PATHINFO_EXTENSION);
        $new_image_name = "food-" . uniqid() . "." . $extension;
        $upload_path = "../backend_files/uploads/foods/$new_image_name";

        if (move_uploaded_file($food_img['tmp_name'], $upload_path)) {
            // Debugging: Check if image uploaded correctly
            echo "Image uploaded successfully to: $upload_path"; // Remove this after testing

            $update = "UPDATE foods SET 
                        food_name = '$food_name', 
                        food_des = '$food_des', 
                        food_price = '$food_price', 
                        food_discount = '$food_discount', 
                        catagory_id = '$catagory_id',
                        food_img_name = '$new_image_name'
                       WHERE id = $id";

            $result = mysqli_query($conn, $update);

            if (!$result) {
                die("Image Update Query Failed: " . mysqli_error($conn));
            }

            $_SESSION['success'] = "Food Updated Successfully with New Image!";
            header("Location: ../backend_files/all_foods.php");
            exit();
        } else {
            die("Failed to upload new image.");
        }
    }
}
?>
