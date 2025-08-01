document.addEventListener('DOMContentLoaded', () => {
    if (typeof userId === 'undefined' || typeof role === 'undefined') {
        console.error('❌ userId o role no definidos. Asegúrate de definirlos antes de incluir este script');
        return;
    }

    function mostrarToast(mensaje) {
        const toast = document.createElement('div');
        toast.textContent = mensaje;
        toast.style.position = 'fixed';
        toast.style.bottom = '20px';
        toast.style.right = '20px';
        toast.style.background = 'rgba(0,0,0,0.7)';
        toast.style.color = 'white';
        toast.style.padding = '10px 20px';
        toast.style.borderRadius = '5px';
        toast.style.zIndex = 10000;
        toast.style.fontSize = '14px';
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.5s';

        document.body.appendChild(toast);

        requestAnimationFrame(() => {
            toast.style.opacity = '1';
        });

        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 500);
        }, 3000);
    }

    // Conectar al servidor de notificaciones
    const socket = io('http://localhost:3000');

    socket.on('connect', () => {
        console.log(`🟢 Conectado a WebSocket con id: ${socket.id}`);

        // Unirse a las salas personalizadas para recibir notificaciones
        socket.emit('joinNotificationRoom', { userId, role });
    });

    socket.on('receiveNotification', (data) => {
        console.log('🔔 Notificación recibida:', data);

        const badge = document.getElementById("noti-count");
        let current = parseInt(badge?.innerText || 0);

        if (badge) {
            badge.innerText = current + 1;
            badge.style.display = "inline-block";
        }

        mostrarToast(`Notificación: ${data.mensaje}`);
    });

    socket.on('disconnect', () => {
        console.log('🔴 Desconectado del servidor de notificaciones');
    });
});
