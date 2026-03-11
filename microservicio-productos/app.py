from flask import Flask 
from dotenv import load_dotenv
from routes.productos import productos_bp
import firebase_admin
from firebase_admin import credentials
import os

# Cargar variables de entorno
load_dotenv()

# Inicializar Firebase
cred = credentials.Certificate(os.getenv('FIREBASE_CREDENTIALS'))
firebase_admin.initialize_app(cred, {
    'databaseURL': os.getenv('FIREBASE_DB_URL')
})

app = Flask(__name__)

# Registrar blueprints (rutas)
app.register_blueprint(productos_bp, url_prefix='/api/productos')

if __name__ == '__main__':
    port = int(os.getenv('FLASK_PORT', 5000))
    app.run(debug=os.getenv('FLASK_ENV') == 'development', port=port)