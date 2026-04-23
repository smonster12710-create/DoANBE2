let deleteId = null;

function showDeleteModal(id) {
    console.log("open modal", id);
    deleteId = id;
    document.getElementById("deleteModal").style.display = "flex";
}

function closeModal() {
    document.getElementById("deleteModal").style.display = "none";
}

function confirmDelete() {
    document.getElementById("delete-form-" + deleteId).submit();
}