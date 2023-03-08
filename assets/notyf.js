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
      background: "gray",
      duration: 5000,
      dismissible: true,
      icon: {
        className: 'material-icons',
        tagName: 'i',
        text: 'warning'
      }
    },
    {
      type: 'warning',
      background: 'orange',
      duration: 5000,
      dismissible: true,
      icon: true,
      icon: {
        className: 'material-icons',
        tagName: 'i',
        text: 'warning'
      }
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

let message = document.querySelectorAll("#notyf-message");
message.forEach(message => {
  if (message.className === 'success') {
    notyf.success(message.innerHTML);
  }
  if (message.className === 'error') {
    notyf.error(message.innerHTML);
  }
  if (message.className === 'info') {
    notyf.open({
      type: "info",
      message: "Ceci est message " + message.innerHTML
    });
  }
  if (message.className === 'warning') {
    notyf.open({
      type: "warning",
      message: "Ceci est message " + message.innerHTML
    });
  }
});