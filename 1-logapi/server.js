const express = require('express');
const bodyParser = require('body-parser');
const { MongoClient } = require('mongodb');
const cors = require('cors');

const app = express();
const port = 3000;
const mongoUrl = 'mongodb://127.0.0.1:27017';
const dbName = 'logDB';
const client = new MongoClient(mongoUrl);

app.use(cors({ origin: 'http://localhost:3001' }));
app.use(bodyParser.json());

async function connectToMongo() {
    try {
        await client.connect();
        console.log('Connected to MongoDB');
    } catch (err) {
        console.error('Could not connect to MongoDB', err);
    }
}

connectToMongo().catch(console.error);

app.post('/log', async (req, res) => {
    const logData = req.body;
    try {
        const db = client.db(dbName);
        const collection = db.collection('logs');
        await collection.insertOne(logData);
        res.status(200).json({ message: 'Log saved to MongoDB', receivedData: logData });
    } catch (err) {
        console.error('Error saving log to MongoDB', err);
        res.status(500).send('Error saving log');
    }
});

app.get('/api/logs', async (req, res) => {
    try {
        const db = client.db(dbName);
        const logs = db.collection('logs');
        const logsList = await logs.find({}).toArray();
        res.status(200).json(logsList);
    } catch (e) {
        res.status(500).json({ message: "Error fetching logs", error: e.message });
    }
});

app.post('/logout', async (req, res) => {
    const { email } = req.body;

    try {
        const db = client.db(dbName);
        const collection = db.collection('logs');

        // Trouver la dernière entrée de connexion pour cet utilisateur
        const lastLogin = await collection.findOne({ email: email, loggerName: "cnxApp" }, { sort: { EventTime: -1 } });

        const logoutTime = new Date();
        let sessionDuration = null;

        if (lastLogin) {
            const loginTime = new Date(lastLogin.EventTime);
            sessionDuration = (logoutTime - loginTime) / 1000; // Durée en secondes
        }

        res.status(200).json({ sessionDuration: sessionDuration });

    } catch (err) {
        console.error('Error handling logout', err);
        res.status(500).send('Error handling logout');
    }
});


app.listen(port, () => {
    console.log(`Log service listening at http://localhost:${port}`);
});

