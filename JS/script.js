function showForm(formId) {
    document.querySelectorAll(".form-box").forEach(form => form.classList.remove("active"));
    document.getElementById(formId).classList.add("active");
} //register/login form swap

function filterCategory(cat) {
    window.location.href = 'user_page.php?category=' + cat;
}//filter used for listings