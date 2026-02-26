<?php include ('includes/header.php'); ?>

<style>
    .beige-table {
        background-color: #FFFFFF;
        color: #000000;
    }
    .beige-table th {
        background-color: #FFFFFF;
        color: #000000;
    }
    .beige-table td {
        background-color: #F5F5F5;
    }
    .beige-table tbody tr:hover {
        background-color: #FFFFFF;
    }
    .card-header {
        background-color: #FFFFFF !important;
        color: #000000;
    }
    .btn-primary {
        background-color: #000000;
        border-color: #000000;
    }
    .btn-primary:hover {
        background-color:#1A1A1A;
        color: #FFFFFF;
    }
    .btn-success {
        background-color: #000000;
        border-color: #000000;
    }
    .btn-danger {
        background-color: #000000;
        border-color: #000000;
    }
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div class="container-fluid px-4">
    <div class="card mt-4 shadow-sm">
        <div class="card-header">
            <h4 class="mb-0">
                <i class="fas fa-coffee"></i> Categories
                <a href="categories-create.php" class="btn btn-primary float-end"> Add Category </a>
            </h4>
        </div>
        <div class="card-body">
            <?php alertMessage(); ?>
            
            <?php
            $categories = getAll('categories');
            if(!$categories){
                echo '<h4>Something Went Wrong!</h4>';
                return false;
            }
            if(mysqli_num_rows($categories) > 0){
            ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered beige-table">
                    <thead> 
                        <tr>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($categories as $item) :?>
                        <tr>
                            <td><?= $item['name'] ?></td>
                            <td>
                                <?php if($item['status'] == 1){
                                    echo '<span class="badge bg-danger">Not Available</span>';
                                }else{
                                    echo '<span class="badge bg-success">Available</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <a href="categories-edit.php?id=<?= $item['id']; ?>" class="btn btn-success btn-sm">Edit</a>
                                <a href="categories-delete.php?id=<?= $item['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table> 
            </div>
            <?php
            } else {
                echo '<h4 class="mb-0">No Record Found</h4>';
            }
            ?>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>