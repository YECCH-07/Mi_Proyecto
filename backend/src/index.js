const express = require('express');
const bodyParser = require('body-parser');
const cors = require('cors');
const usersRoutes = require('./routes/users');
const authRoutes = require('./routes/auth');
const denunciasRoutes = require('./routes/denuncias');

const app = express();
const port = 3000;

app.use(bodyParser.json());
app.use(cors());

app.use('/api/users', usersRoutes);
app.use('/api/auth', authRoutes);
app.use('/api/denuncias', denunciasRoutes);


app.listen(port, () => {
    console.log(`Server is running on http://localhost:${port}`);
});