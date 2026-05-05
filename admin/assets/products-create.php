 <?php include ('includes/header.php'); ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div class="container-fluid px-4">
    <div class="card mt-4 shadow-sm beige-card">
        <div class="card-header beige-card-header">
            <h4 class="mb-0">
                <i class="fas fa-cash-register"></i> Add Product
                <a href="products.php" class="btn btn-secondary float-end"> Back </a>
            </h4>
        </div>
        <div class="card-body">

            <?php alertMessage(); ?>

            <form action="code.php" method="POST" enctype="multipart/form-data">

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label> Select Category</label>
                        <select name="category_id" class="form-select">
                            <option value=""> Select Category</option>
                            <?php
                            $categories = getAll('categories');
                            if($categories){
                                if(mysqli_num_rows($categories) > 0){
                                    foreach($categories as $cateItem){
                                        echo '<option value="'.$cateItem['id'].'">'.$cateItem['name'].'</option>';
                                    }
                                }else{
                                    echo '<option value="">No Categories Found</option>'; 
                                }  
                            }else{
                            echo '<option value="">Something Went Wrong!</option>'; 
                            }
                            ?>
                    </select>
                    </div>
                <div class="col-md-12 mb-3">
                        <label for=""> Product Name *</label>
                        <input type="text" name="name" required class="form-control" />
                    </div>
                <div class="col-md-6 mb-3">
                        <label for="">Price (12oz) *</label>
                        <input type="number" step="0.01" min="0" name="price_12oz" required class="form-control" />
                    </div>
                <div class="col-md-6 mb-3">
                        <label for="">Price (16oz) *</label>
                        <input type="number" step="0.01" min="0" name="price_16oz" required class="form-control" />
                    </div>
                <div class="col-md-12 mb-3">
                        <label for="">Image *</label>
                        <input type="file" name="image" class="form-control" />
                    </div>
<style>

  .status-container {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    border: 1px solid #d9d9d9;
    border-radius: 999px;
    background: #f8f9fb;
    user-select: none;
    cursor: pointer;
    transition: border-color .2s ease, box-shadow .2s ease, background-color .2s ease;
  }

  .status-container:hover {
    border-color: #b8bec8;
    background: #f2f4f7;
  }

  .status-container:focus-within {
    border-color: #2f6df6;
    box-shadow: 0 0 0 3px rgba(47, 109, 246, 0.18);
  }

  .status-checkbox {
    position: absolute;
    opacity: 0;
    width: 1px;
    height: 1px;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    border: 0;
  }

  .custom-checkbox {
    width: 38px;
    height: 22px;
    background-color: #16a34a;
    border-radius: 999px;
    position: relative;
    transition: background-color .2s ease;
    flex-shrink: 0;
  }

  .custom-checkbox::before {
    content: "";
    width: 16px;
    height: 16px;
    background-color: #ffffff;
    border-radius: 50%;
    position: absolute;
    top: 3px;
    left: 3px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    transition: transform .2s ease;
  }

  .status-checkbox:checked + .custom-checkbox {
    background-color: #dc2626;
  }

  .status-checkbox:checked + .custom-checkbox::before {
    transform: translateX(16px);
  }

  .status-label {
    font-weight: 600;
    font-size: 15px;
    line-height: 1;
    color: #475569;
  }
</style>

    <label class="status-container">
    <input type="checkbox" class="status-checkbox" name="status">
    <div class="custom-checkbox"></div>
    <span class="status-label">Available</span>
    </label>

<script>
    const checkbox = document.querySelector(".status-checkbox");
    const label = document.querySelector(".status-label");

    checkbox.addEventListener("change", function () {
        label.textContent = this.checked ? "Not Available" : "Available";
    });
</script>
                    <div class="col-md-6 mb-3 text-end">
                        <br/>
                        <button type="submit" name="saveProduct" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>
