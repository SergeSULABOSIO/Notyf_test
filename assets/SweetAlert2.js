// ES6 Modules or TypeScript
import Swal from 'sweetalert2'



document.getElementById("bt1").addEventListener("click", function () {
  const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 2000,
    timerProgressBar: true,
    didOpen: (toast) => {
      toast.addEventListener('mouseenter', Swal.stopTimer)
      toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
  })

  Toast.fire({
    icon: 'success',
    title: 'Enregistrement effectué avec succès!'
  })
});


document.getElementById("bt2").addEventListener("click", function () {
  Swal.fire({
    title: 'Suppression?',
    text: "Etes-vous sûre de vouloir supprimer ceci?",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Oui, supprimez-le'
  }).then((result) => {
    if (result.isConfirmed) {
      Swal.fire(
        'Supprimé avec succès',
        'Enregistrement supprimé avec succès!',
        'success'
      )
    }
  })
});



