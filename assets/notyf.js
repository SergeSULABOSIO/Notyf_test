import { Notyf } from 'notyf';
import 'notyf/notyf.min.css'; // for React, Vue and Svelte


// const notyf = new Notyf();



const notyf = new Notyf({
    duration: 5000,
    position: {
      x: 'right',
      y: 'top',
    },
    types: [
      {
        type: 'info',
        background: "yellow",
        duration: 5000,
        dismissible: true
      },
      {
        type: 'warning',
        background: 'orange',
        duration: 5000,
        dismissible: true
      }
    ]
  });

//notyf.error('Please fill out the form');


// const notification = notyf.success('Données enregistrées avec succès! Cliquez svp!');
// notification.on('click', ({target, event}) => {
//   // target: the notification being clicked
//   // event: the mouseevent
//   window.location.href = '/bobo sula !';
// });

let message = document.querySelectorAll("#message_info");