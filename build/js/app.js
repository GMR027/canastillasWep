console.log('Pagina web de Canastillas de la Baja');
document.addEventListener('DOMContentLoaded', function () {
  scrollNav()
})

function scrollNav() {
  const linksNavegacion = document.querySelectorAll('.navbar a')
  linksNavegacion.forEach(link => {
    link.addEventListener('click', event => {
      const href = event.target.getAttribute('href')
      if (!href || !href.startsWith('#')) return

      event.preventDefault()
      const seccionApuntada = document.querySelector(href)
      if (seccionApuntada) {
        seccionApuntada.scrollIntoView({behavior: 'smooth'})
      }
    })
  })
}


//Funcion para mostar la informacion adicional
function mostrarInformacion(idDeInformacion) {
  const informacion = document.querySelector(`#${idDeInformacion}`);
  if (informacion) {
    informacion.style.display = 'block'; //Mostrar la informacion
  }
}

//Funcion para cerrar la informacion adicional
function cerrarInformacion(idDeInformacion) {
  const informacion = document.querySelector(`#${idDeInformacion}`);
  if (informacion) {
    informacion.style.display = 'none'; //Ocultar la informacion
  }
}

//Asignar evento al boton de +informacion
document.querySelectorAll('.info-btn').forEach(function (boton) {
  boton.addEventListener('click', function() {
    const idDeInformacion = boton.getAttribute('data-target'); //Obtenemos el ID de la informacion
    mostrarInformacion(idDeInformacion); //Mostramos la informacion correspondiente
  })
})


//Asignar evento al boton de cerrar informacion
document.querySelectorAll('.cerrar-info').forEach(function (boton) {
  boton.addEventListener('click', function() {
    const infAdicional = boton.closest('.informacion'); 
    if(infAdicional) {
      cerrarInformacion(infAdicional.id);
    }
  })
})


const costoSinIva = document.getElementById('costoSinIva');
if (costoSinIva) {
  costoSinIva.addEventListener('input', function() { //Agregamos un evento de escucha para cuando el usuario ingrese un valor en el campo de costo sin IVA
    var sinIva = parseFloat(this.value); //estamos obteniendo el valor del input y convirtiendolo a numero decimal
    if (!isNaN(sinIva)) { //Si el valor es un numero valido, calculamos el costo con IVA
      document.getElementById('costoConIva').value = (sinIva * 1.16).toFixed(2); //Calculamos el costo con IVA multiplicando el costo sin IVA por 1.16 (IVA del 16%) y redondeamos a 2 decimales
    } else {
      document.getElementById('costoConIva').value = ''; //Si el valor no es un numero valido, dejamos el campo de costo con IVA vacio
    }
  });
}

document.addEventListener('click', function(e) {
  if (e.target.classList.contains('eliminar')) {
    document.getElementById('modal-id').value = e.target.getAttribute('data-id');
    document.getElementById('modal-overlay').style.display = 'flex';
  }
  if (e.target.id === 'btn-cancelar') {
    document.getElementById('modal-overlay').style.display = 'none';
  }
});
