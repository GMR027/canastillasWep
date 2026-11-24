// Fichas tecnicas en <dialog>
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-abrir-modal]').forEach((boton) => {
    boton.addEventListener('click', () => {
      const modal = document.getElementById(boton.dataset.abrirModal);
      if (!modal) return;
      modal.showModal();
      document.body.classList.add('menu-abierto');
    });
  });

  document.querySelectorAll('dialog.modal').forEach((modal) => {
    // Cerrar con el boton
    modal.querySelectorAll('[data-cerrar-modal]').forEach((b) => b.addEventListener('click', () => modal.close()));

    // Cerrar al hacer clic en el fondo
    modal.addEventListener('click', (e) => {
      if (e.target === modal) modal.close();
    });

    // Esc lo maneja el navegador; solo liberamos el scroll
    modal.addEventListener('close', () => document.body.classList.remove('menu-abierto'));
  });
});
