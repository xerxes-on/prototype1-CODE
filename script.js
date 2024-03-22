
let error = document.getElementById('alert')
if(error.style.display !== 'none'){
    setTimeout(function () {
    error.style.display = "none";
    window.location.href = 'index.php';
}, 4000);
}




