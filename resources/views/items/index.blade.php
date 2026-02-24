@extends('layouts.app')

@section('title', 'Items List')

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title">
                <i class="fas fa-box me-2"></i>Items
            </h2>
        </div>
        <div class="col-auto ms-auto">
            <div class="btn-list">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#itemModal" id="addNewItem">
                    <i class="fas fa-plus me-2"></i>Add New Item
                </button>
                <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-download me-2"></i>Export
                    </button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="{{ route('items.export.excel') }}">
                            <i class="fas fa-file-excel me-2"></i>Excel
                        </a>
                        <a class="dropdown-item" href="{{ route('items.export.pdf') }}">
                            <i class="fas fa-file-pdf me-2"></i>PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-bordered" id="items-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Category</th>
                    <th width="150">Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="itemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="itemForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="itemId" name="itemId">
                    
                    <div class="mb-3">
                        <label class="form-label">Code</label>
                        <input type="text" class="form-control" id="code" name="code" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Price</label>
                        <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select class="form-control" id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="Electronics">Electronics</option>
                            <option value="Furniture">Furniture</option>
                            <option value="Stationery">Stationery</option>
                            <option value="Others">Others</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Item</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#items-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('items.data') }}",
        columns: [
            {data: 'id', name: 'id'},
            {data: 'code', name: 'code'},
            {data: 'name', name: 'name'},
            {data: 'description', name: 'description'},
            {data: 'quantity', name: 'quantity'},
            {data: 'price', name: 'price'},
            {data: 'category', name: 'category'},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ]
    });

    // Reset form when add new button clicked
    $('#addNewItem').click(function() {
        $('#itemForm')[0].reset();
        $('#itemId').val('');
        $('.modal-title').text('Add New Item');
    });

    // Save item
    $('#itemForm').submit(function(e) {
        e.preventDefault();
        
        var id = $('#itemId').val();
        var url = id ? '{{ route("items.update", ":id") }}'.replace(':id', id) : '{{ route("items.store") }}';
        var method = id ? 'PUT' : 'POST';
        
        $.ajax({
            url: url,
            type: method,
            data: $(this).serialize(),
            success: function(response) {
                $('#itemModal').modal('hide');
                $('#itemForm')[0].reset();
                table.ajax.reload();
                Swal.fire('Success!', 'Item saved successfully.', 'success');
            },
            error: function(xhr) {
                Swal.fire('Error!', 'Something went wrong.', 'error');
            }
        });
    });

    // Edit item
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        $.get('{{ route("items.edit", ":id") }}'.replace(':id', id), function(data) {
            $('#itemId').val(data.id);
            $('#code').val(data.code);
            $('#name').val(data.name);
            $('#description').val(data.description);
            $('#quantity').val(data.quantity);
            $('#price').val(data.price);
            $('#category').val(data.category);
            $('.modal-title').text('Edit Item');
            $('#itemModal').modal('show');
        });
    });

    // Delete item
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("items.destroy", ":id") }}'.replace(':id', id),
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        table.ajax.reload();
                        Swal.fire('Deleted!', 'Item has been deleted.', 'success');
                    }
                });
            }
        });
    });
});
</script>
@endpush