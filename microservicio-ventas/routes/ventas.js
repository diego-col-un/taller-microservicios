const express = require('express');
const router = express.Router();
const mongoose = require('mongoose');

// Schema de venta
const ventaSchema = new mongoose.Schema({
    usuario_id: { type: String, required: true },
    producto_id: { type: String, required: true },
    cantidad:    { type: Number, required: true },
    total:       { type: Number, required: true },
    fecha:       { type: Date, default: Date.now }
});

const Venta = mongoose.model('Venta', ventaSchema);

// ─────────────────────────────────────────
// GET /api/ventas - Listar todas las ventas
// ─────────────────────────────────────────
router.get('/', function(req, res) {
    Venta.find()
        .then(function(ventas) {
            res.json({ success: true, data: ventas });
        })
        .catch(function(err) {
            res.status(500).json({ success: false, message: err.message });
        });
});

// ─────────────────────────────────────────
// GET /api/ventas/usuario/:usuario_id
// ─────────────────────────────────────────
router.get('/usuario/:usuario_id', function(req, res) {
    Venta.find({ usuario_id: req.params.usuario_id })
        .then(function(ventas) {
            res.json({ success: true, data: ventas });
        })
        .catch(function(err) {
            res.status(500).json({ success: false, message: err.message });
        });
});

// ─────────────────────────────────────────
// GET /api/ventas/fecha/:fecha
// ─────────────────────────────────────────
router.get('/fecha/:fecha', function(req, res) {
    var inicio = new Date(req.params.fecha);
    var fin = new Date(req.params.fecha);
    fin.setDate(fin.getDate() + 1);

    Venta.find({ fecha: { $gte: inicio, $lt: fin } })
        .then(function(ventas) {
            res.json({ success: true, data: ventas });
        })
        .catch(function(err) {
            res.status(500).json({ success: false, message: err.message });
        });
});

// ─────────────────────────────────────────
// POST /api/ventas - Registrar venta
// ─────────────────────────────────────────
router.post('/', function(req, res) {
    var usuario_id  = req.body.usuario_id;
    var producto_id = req.body.producto_id;
    var cantidad    = req.body.cantidad;
    var total       = req.body.total;

    if (!usuario_id || !producto_id || !cantidad || !total) {
        return res.status(400).json({
            success: false,
            message: 'usuario_id, producto_id, cantidad y total son requeridos'
        });
    }

    var venta = new Venta({ usuario_id, producto_id, cantidad, total });

    venta.save()
        .then(function(ventaGuardada) {
            res.status(201).json({ success: true, data: ventaGuardada });
        })
        .catch(function(err) {
            res.status(500).json({ success: false, message: err.message });
        });
});

module.exports = router;