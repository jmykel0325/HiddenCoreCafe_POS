<?php include ('includes/header.php'); ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    .hc-categories-page.hc-categories-ref {
        max-width: 1520px;
        margin: 0 auto;
    }

    .hc-categories-ref .card {
        border-radius: 24px !important;
        overflow: hidden;
    }

    .hc-categories-ref .hc-categories-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .hc-categories-ref .hc-categories-head-copy {
        display: grid;
        gap: .38rem;
    }

    .hc-categories-ref .hc-categories-kicker {
        display: inline-flex;
        width: max-content;
        align-items: center;
        gap: .38rem;
        padding: .3rem .62rem;
        border-radius: 999px;
        background: #fff1e7;
        color: #b8612b;
        border: 1px solid #ffd8bf;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .hc-categories-ref .card-header h4 {
        margin: 0;
        font-size: 2rem;
        font-weight: 900;
        letter-spacing: -.02em;
    }

    .hc-categories-ref .hc-categories-subtitle {
        margin: 0;
        color: #7A726B;
        font-size: .94rem;
        font-weight: 500;
    }

    .hc-categories-ref .card-header .btn {
        min-height: 42px;
        padding: .5rem 1rem !important;
    }

    .hc-categories-ref .table-responsive {
        border-radius: 16px;
        overflow: hidden;
    }

    .hc-categories-ref table.table thead th {
        background: #fdf8f4 !important;
        color: #423b34 !important;
        font-size: .95rem;
        font-weight: 800 !important;
        letter-spacing: .01em;
        vertical-align: middle !important;
    }

    .hc-categories-ref table.table tbody td {
        padding: .95rem .95rem !important;
        font-size: .95rem;
    }

    .hc-categories-ref table.table tbody td:first-child {
        color: #2F2A25 !important;
        font-weight: 800 !important;
    }

    .hc-categories-ref .badge {
        min-height: 28px;
        display: inline-flex;
        align-items: center;
        border-radius: 999px !important;
        padding: .36rem .68rem !important;
        font-weight: 700 !important;
    }

    .hc-categories-ref .btn.btn-sm {
        min-height: 30px !important;
        padding: .22rem .62rem !important;
        font-size: .76rem !important;
        font-weight: 700 !important;
        line-height: 1.1 !important;
    }

    .hc-categories-ref .btn.btn-success.btn-sm {
        background: var(--primary, #FF7A1A) !important;
        border-color: var(--primary, #FF7A1A) !important;
        color: #fff !important;
    }

    .hc-categories-ref .btn.btn-success.btn-sm:hover {
        background: var(--primary-hover, #E96A0C) !important;
        border-color: var(--primary-hover, #E96A0C) !important;
    }

    .hc-categories-ref .btn.btn-danger.btn-sm {
        background: #ef4444 !important;
        border-color: #ef4444 !important;
        color: #fff !important;
    }

    .hc-categories-ref .hc-action-row {
        display: flex;
        flex-wrap: wrap;
        gap: .34rem;
        align-items: center;
        min-width: max-content;
    }

    .hc-categories-ref .hc-action-row .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: auto;
        min-width: 52px;
        white-space: nowrap;
    }

    .hc-categories-ref .hc-empty-state {
        text-align: center;
        padding: 1.6rem 1rem;
        border: 1px dashed #e9e3dc;
        border-radius: 16px;
        background: #fffdfb;
        color: #7A726B;
        font-weight: 600;
    }
</style>

<div class="container-fluid px-4 hc-admin-page hc-categories-page hc-categories-ref">
    <div class="card mt-4 shadow-sm">
        <div class="card-header">
            <div class="hc-categories-head">
                <div class="hc-categories-head-copy">
                    <span class="hc-categories-kicker"><i class="fas fa-mug-hot"></i> Menu Control</span>
                    <h4><i class="fas fa-coffee"></i> Categories</h4>
                    <p class="hc-categories-subtitle">Manage category groups used for product organization and filtering.</p>
                </div>
                <a href="categories-create.php" class="btn btn-primary">Add Category</a>
            </div>
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
                                <div class="hc-action-row">
                                    <a href="categories-edit.php?id=<?= $item['id']; ?>" class="btn btn-success btn-sm">Edit</a>
                                    <a href="categories-delete.php?id=<?= $item['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table> 
            </div>
            <?php
            } else {
                echo '<div class="hc-empty-state">No categories found yet. Add your first category to get started.</div>';
            }
            ?>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>
