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