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

  socket.on('joinRoom', (chat_id) => {
    const room = 'chat_' + chat_id;
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
      db.query(
        "INSERT INTO messg_tech_admin (apply_id, emisor, message, date) VALUES (?, ?, ?, NOW())",
        [chat_id, sender, mensaje],
        (err) => {
          if (err) {
            console.error("❌ Error al guardar mensaje admin:", err);
            return;
          }

          io.to('apply_' + chat_id).emit('newMessage', {
            chat_id,
            sender,
            mensaje,
            tipo_chat,
            timestamp: new Date().toISOString()
          });
        }
      );
    } else if (tipo_chat === 'usuario') {
      db.query(
        "INSERT INTO messg_tech_user (chat_id, sender, message, timestamp) VALUES (?, ?, ?, NOW())",
        [chat_id, sender, mensaje],
        (err) => {
          if (err) {
            console.error("❌ Error al guardar mensaje usuario:", err);
            return;
          }

          io.to('chat_' + chat_id).emit('newMessage', {
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
