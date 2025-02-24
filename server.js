import express from "express";
import { createServer } from "http";
import { Server } from "socket.io";

const app = express();
app.use(express.json());

const server = createServer(app);
const io = new Server(server, {
    cors: {
        origin: "*",
    },
});

const users = {}; // Store connected users

io.on("connection", (socket) => {
    const user_id = socket.handshake.query.user_id;

    if (user_id) {
        users[user_id] = socket.id;
        console.log(`User Connected: ${user_id} (Socket ID: ${socket.id})`);
    }

    // Handle private messages
    socket.on("private-message", ({ receiver_id, message,image }) => {
        const sender_id = Object.keys(users).find(key => users[key] === socket.id); // Get sender ID

        if (!sender_id) {
            console.log("Sender not identified.");
            return;
        }

        console.log(`Private Message from ${sender_id} to ${receiver_id}: ${message},Image: ${image}`);

        // Send message only if receiver is online
        if (users[receiver_id]) {
            io.to(users[receiver_id]).emit("private-message", {
                sender_id,
                message,
                image,
            });
        } else {
            console.log(`User ${receiver_id} is offline.`);
        }
    });

    // Handle user disconnection
    socket.on("disconnect", () => {
        console.log(` User Disconnected: ${socket.id}`);
        for (const [id, socketId] of Object.entries(users)) {
            if (socketId === socket.id) {
                delete users[id];
                break;
            }
        }
        console.log("Active Users:", users);
    });
});

server.listen(3000, "127.0.0.1", () => {
    console.log("Socket.IO Server Running at http://127.0.0.1:3000");
});
