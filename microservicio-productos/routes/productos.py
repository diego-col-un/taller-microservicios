from flask import Blueprint, jsonify, request
from firebase_admin import db

productos_bp = Blueprint('productos', __name__)

# ─────────────────────────────────────────
# GET /api/productos — Listar todos
# ─────────────────────────────────────────
@productos_bp.route('/', methods=['GET'])
def get_productos():
    ref = db.reference('productos')
    productos = ref.get()

    if not productos:
        return jsonify({
            "success": True,
            "data": []
        }), 200

    # Firebase devuelve un dict, lo convertimos a lista
    lista = [{"id": k, **v} for k, v in productos.items()]

    return jsonify({
        "success": True,
        "data": lista
    }), 200

# ─────────────────────────────────────────
# GET /api/productos/<id> — Obtener uno
# ─────────────────────────────────────────
@productos_bp.route('/<id>', methods=['GET'])
def get_producto(id):
    ref = db.reference('productos')
    producto = ref.child(id).get()

    if not producto:
        return jsonify({
            "success": False,
            "message": "Producto no encontrado"
        }), 404

    return jsonify({
        "success": True,
        "data": {"id": id, **producto}
    }), 200

# ─────────────────────────────────────────
# POST /api/productos — Crear producto
# ─────────────────────────────────────────
@productos_bp.route('/', methods=['POST'])
def create_producto():
    ref = db.reference('productos')
    body = request.get_json()

    if not body or not body.get('nombre') or not body.get('precio'):
        return jsonify({
            "success": False,
            "message": "nombre y precio son requeridos"
        }), 400

    nuevo = {
        "nombre": body['nombre'],
        "precio": body['precio'],
        "stock": body.get('stock', 0)
    }

    # Firebase genera el ID automáticamente
    nuevo_ref = ref.push(nuevo)

    return jsonify({
        "success": True,
        "data": {"id": nuevo_ref.key, **nuevo}
    }), 201

# ─────────────────────────────────────────
# PUT /api/productos/<id>/stock — Actualizar stock
# ─────────────────────────────────────────
@productos_bp.route('/<id>/stock', methods=['PUT'])
def update_stock(id):
    ref = db.reference('productos')
    producto = ref.child(id).get()

    if not producto:
        return jsonify({
            "success": False,
            "message": "Producto no encontrado"
        }), 404

    body = request.get_json()
    cantidad = body.get('cantidad', 0)

    if producto['stock'] < cantidad:
        return jsonify({
            "success": False,
            "message": "Stock insuficiente"
        }), 400

    nuevo_stock = producto['stock'] - cantidad
    ref.child(id).update({"stock": nuevo_stock})

    return jsonify({
        "success": True,
        "data": {"id": id, **producto, "stock": nuevo_stock}
    }), 200

# ─────────────────────────────────────────
# DELETE /api/productos/<id> — Eliminar producto
# ─────────────────────────────────────────
@productos_bp.route('/<id>', methods=['DELETE'])
def delete_producto(id):
    ref = db.reference('productos')
    producto = ref.child(id).get()

    if not producto:
        return jsonify({
            "success": False,
            "message": "Producto no encontrado"
        }), 404

    ref.child(id).delete()

    return jsonify({
        "success": True,
        "message": "Producto eliminado correctamente"
    }), 200