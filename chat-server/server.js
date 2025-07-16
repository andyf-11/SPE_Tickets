const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const mysql = require('mysql2');
const cors = require('cors');
require('dotenv').config();

console.log("🔍 ENV cargado:", {
  DB_HOST: process.env.DB_HOST,
  DB_USER: process.env.DB_USER,
  DB_PASS: process.env.DB_PASS,
  DB_NAME: process.env.DB_NAME
});

const app = express();
app.use(cors());

const server = http.createServer(app);
const io = new Server(server, {
  cors: {
    origin: [process.env.APP_DOMAIN],
    methods: ['GET', 'POST']
  }
});

const db = mysql.createConnection({
  host: process.env.DB_HOST,
  user: process.env.DB_USER,
  password: process.env.DB_PASS,
  database: process.env.DB_NAME
});

db.connect(err => {
  if (err) {
    console.error("❌ Error conectando a MySQL:", err);
  } else {
    console.log("✅ Conectado a MySQL");
  }
});

io.on('connection', (socket) => {
  console.log('🟢 Usuario conectado');

  // El frontend ya envía el nombre completo de la sala: apply_15, chat_12, etc.
  socket.on('joinRoom', (room) => {
    socket.join(room);
    console.log(`🔗 Usuario se unió a la sala ${room}`);
  });

  socket.on('sendMessage', (data) => {
    const { chat_id, tipo_chat, sender, mensaje } = data;

    if (!chat_id || !tipo_chat || !sender || !mensaje) {
      console.warn("⚠️ Datos incompletos en sendMessage:", data);
      return;
    }

    if (tipo_chat === 'admin') {
      // Chat técnico ↔ admin
      db.query(
        "INSERT INTO messg_tech_admin (apply_id, emisor, message, date) VALUES (?, ?, ?, NOW())",
        [chat_id, sender, mensaje],
        (err) => {
          if (err) {
            console.error("❌ Error al guardar mensaje admin:", err);
            return;
          }

          const sala = `apply_${chat_id}`;
          console.log(`📤 Mensaje técnico/admin a sala ${sala}:`, mensaje);

          io.to(sala).emit('newMessage', {
            chat_id,
            sender,
            mensaje,
            tipo_chat,
            timestamp: new Date().toISOString()
          });
        }
      );
    } else if (tipo_chat === 'usuario') {
      // Chat técnico ↔ usuario
      db.query(
        "INSERT INTO messg_tech_user (chat_id, sender, message, timestamp) VALUES (?, ?, ?, NOW())",
        [chat_id, sender, mensaje],
        (err) => {
          if (err) {
            console.error("❌ Error al guardar mensaje usuario:", err);
            return;
          }

          const sala = `chat_${chat_id}`;
          console.log(`📤 Mensaje técnico/usuario a sala ${sala}:`, mensaje);

          io.to(sala).emit('newMessage', {
            chat_id,
            sender,
            mensaje,
            tipo_chat,
            timestamp: new Date().toISOString()
          });
        }
      );
    } else {
      console.warn("❌ Tipo de chat desconocido:", tipo_chat);
    }
  });

  socket.on('disconnect', () => {
    console.log('🔴 Usuario desconectado');
  });
});

server.listen(3000, () => {
  console.log('🚀 Servidor WebSocket escuchando en http://localhost:3000');
});
