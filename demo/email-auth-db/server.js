require("dotenv").config();
const express = require("express");
const mongoose = require("mongoose");
const bcrypt = require("bcryptjs");

const User = require("./models/User");

const app = express();
app.use(express.json());

const DB_STATE_LABELS = {
  0: "Disconnected",
  1: "Connected",
  2: "Connecting",
  3: "Disconnecting",
};

function getDbStatus (){
  const readyState = mongoose.connection.readyState;
  return {
    readyState,
    status: DB_STATE_LABELS[readyState] || "Unknown",
  };

}

function requireDbConnection(req, res, next) {
  const { readyState, status } = getDbStatus();
  if (readyState !== 1) {
    return res.status(503).json({
      error: "Database connection is not established",
      dbStatus: status,
      readyState,
    });
  }
  next();
    
}
 
//ROOT HEALTH 

app.get("/health", (req, res) => {
  const db = getDbStatus();

  res.json({
    ok: true,
    service: "email-auth-db",
    uptimeSeconds: Math.floor(process.uptime()),
    timestamp: new Date().toISOString(),
    db,
  });
});

if(!process.env.MONGO_URI){
  throw new Error("MONGO_URI is not defined in .env file");
}
const PORT = process.env.PORT || 5001;
const DB_RETRY_MS = 10000;

async function connectWithRetry() {
  try {
    await mongoose.connect(process.env.MONGO_URI);
    console.log("Connected to MongoDB");
  } catch (err) {
    console.error("MONGODB connection error:", err.message);
if(err.name==="MongooseServerSelectionError"){
      console.error(
        "Check MongoDB Atlas Network Access."
      );
    }

    console.error(`Retrying MongoDB connection in ${DB_RETRY_MS / 1000}s...`);
    setTimeout(connectDatabase, DB_RETRY_MS);
  }
}

app.listen(PORT, () => console.log(`Server running on port ${PORT}`));
connectDatabase();


/* Register */
app.post("/register", async (req, res) => {
  try {
    const { email, password } = req.body;

    const existingUser = await User.findOne({ email });
    if (existingUser) {
      return res.status(400).json({ error: "Email already exists" });
    }
    
  const salt = await bcrypt.genSalt(10);
    const hashedPassword = await bcrypt.hash(password, salt);
    
    const user = new User({
      email,
      password: hashedPassword,
    });
    await user.save();

    res.status(201).json({ message: "User registered successfully" });
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: err.message });
  }
});

/* Login */
app.post("/login", requireDbConnection, async (req, res) => {
  try {
    const { email, password } = req.body;

    const user = await User.findOne({ email });
    if (!user) {
      return res.status(404).json({ error: "User not found" });
    }

    const isMatch = await bcrypt.compare(password, user.password);
    if (!isMatch) {
      return res.status(400).json({ error: "Wrong password" });
    }

    res.json({ message: "Login successful" });
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: err.message });
  }
});

app.get("/users", requireDbConnection, async (req, res) => {
  try {
    const users = await User.find();
    res.json(users);
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});



app.get("/db-ping", async (req, res) => {
  try {
    const db = getDbStatus();

    if (db.readyState !== 1) {
      return res.status(503).json({
        ok: false,
        status: db.status,
        readyState: db.readyState,
      });
    }

    const pingResult = await mongoose.connection.db.admin().command({ ping: 1 });

    res.json({
      ok: pingResult.ok === 1,
      status: "connected",
      readyState: mongoose.connection.readyState,
      ping: pingResult,
    });
  } catch (err) {
    res.status(500).json({
      ok: false,
      status: "error",
      error: err.message,
    });
  }
});
 