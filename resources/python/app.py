from flask import Flask, Response, request, jsonify
from flask_cors import CORS
import cv2
import numpy as np
import os
import json
import base64
from deepface import DeepFace
import threading
import time

app = Flask(__name__)
CORS(app)

# folder untuk simpan data wajah
UPLOAD_FOLDER = 'known_faces'
DATABASE_FILE = 'face_database.json'
os.makedirs(UPLOAD_FOLDER, exist_ok=True)

# variable global untuk database wajah
face_database = {}

# cache untuk identifikasi
identification_cache = {}
cache_lock = threading.Lock()

# fungsi load database dari json
def load_database():
    global face_database
    if os.path.exists(DATABASE_FILE):
        with open(DATABASE_FILE, 'r') as f:
            face_database = json.load(f)
        print(f"berhasil load {len(face_database)} data wajah")
    else:
        face_database = {}
        print("belum ada database wajah")

# fungsi simpan database ke json
def save_database():
    with open(DATABASE_FILE, 'w') as f:
        json.dump(face_database, f, indent=2)
    print("database berhasil disimpan")

# load database saat startup
load_database()

# endpoint untuk upload foto training
@app.route('/api/train', methods=['POST'])
def train_face():
    try:
        if 'image' not in request.files:
            return jsonify({'error': 'tidak ada file gambar'}), 400

        if 'name' not in request.form:
            return jsonify({'error': 'nama harus diisi'}), 400

        name = request.form['name']
        image_file = request.files['image']

        # baca gambar
        image_bytes = image_file.read()
        nparr = np.frombuffer(image_bytes, np.uint8)
        image = cv2.imdecode(nparr, cv2.IMREAD_COLOR)

        # verifikasi ada wajah di gambar
        try:
            faces = DeepFace.extract_faces(img_path=image, detector_backend='opencv', enforce_detection=True)
            if len(faces) == 0:
                return jsonify({'error': 'tidak ada wajah terdeteksi'}), 400
            if len(faces) > 1:
                return jsonify({'error': 'terdeteksi lebih dari 1 wajah, gunakan foto dengan 1 wajah saja'}), 400
        except Exception as e:
            return jsonify({'error': f'gagal deteksi wajah: {str(e)}'}), 400

        # simpan gambar ke folder
        if name not in face_database:
            face_database[name] = []

        count = len(face_database[name])
        filename = f"{name}_{count + 1}.jpg"
        filepath = os.path.join(UPLOAD_FOLDER, filename)
        cv2.imwrite(filepath, image)

        # simpan path ke database
        face_database[name].append(filepath)
        save_database()

        # clear cache
        with cache_lock:
            identification_cache.clear()

        total = sum(len(images) for images in face_database.values())

        return jsonify({
            'success': True,
            'message': f'wajah {name} berhasil ditambahkan',
            'total_faces': total
        })

    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/api/delete/<n>', methods=['DELETE'])
def delete_face(name):
    if name not in face_database:
        return jsonify({'error': f'tidak ada data untuk nama {name}'}), 404

    for filepath in face_database[name]:
        if os.path.exists(filepath):
            os.remove(filepath)

    count = len(face_database[name])
    del face_database[name]
    save_database()

    with cache_lock:
        identification_cache.clear()

    return jsonify({
        'success': True,
        'message': f'{count} data wajah {name} berhasil dihapus'
    })

@app.route('/api/faces', methods=['GET'])
def list_faces():
    unique_names = list(face_database.keys())
    face_counts = {name: len(images) for name, images in face_database.items()}
    total = sum(face_counts.values())

    return jsonify({
        'total': total,
        'unique_names': unique_names,
        'counts': face_counts
    })

# queue untuk identifikasi async
identification_queue = []
queue_lock = threading.Lock()

def identification_worker():
    """
    worker thread untuk proses identifikasi di background
    """
    while True:
        with queue_lock:
            if identification_queue:
                task = identification_queue.pop(0)
            else:
                task = None

        if task:
            face_id, face_img = task
            name, confidence = identify_face_sync(face_img)

            with cache_lock:
                identification_cache[face_id] = {
                    'name': name,
                    'confidence': confidence,
                    'timestamp': time.time()
                }
        else:
            time.sleep(0.1)

def identify_face_sync(face_img):
    if not face_database:
        return "unknown", 0

    try:
        # jangan resize manual
        face_img_rgb = cv2.cvtColor(face_img, cv2.COLOR_BGR2RGB)

        best_match = "unknown"
        best_distance = 999

        for name, image_paths in face_database.items():
            for db_img_path in image_paths:

                try:
                    result = DeepFace.verify(
                        img1_path=face_img_rgb,
                        img2_path=db_img_path,
                        model_name='VGG-Face',
                        detector_backend='opencv',   # biarkan DeepFace cropping sendiri
                        enforce_detection=False
                    )

                    distance = result['distance']

                    if distance < best_distance:
                        best_distance = distance
                        best_match = name if result['verified'] else "unknown"

                except:
                    continue

        # hitung confidence menggunakan skala VGG-Face
        if best_match == "unknown":
            confidence = 0
        else:
            # skala empiris untuk VGGFace
            confidence = max(0, min(100, (0.6 - best_distance) / 0.6 * 100))

        return best_match, round(confidence, 2)

    except Exception:
        return "unknown", 0

# start worker thread
worker_thread = threading.Thread(target=identification_worker, daemon=True)
worker_thread.start()

# kamera global
camera = None
last_faces = []
frame_count = 0

def init_camera():
    global camera
    if camera is None or not camera.isOpened():
        camera = cv2.VideoCapture(0)
        camera.set(cv2.CAP_PROP_FRAME_WIDTH, 1280)
        camera.set(cv2.CAP_PROP_FRAME_HEIGHT, 720)
        camera.set(cv2.CAP_PROP_FPS, 60)

def generate_frames():
    global last_faces, frame_count
    init_camera()

    face_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_default.xml')

    while True:
        success, frame = camera.read()
        if not success:
            break

        frame_count += 1

        # deteksi wajah setiap 5 frame (tanpa resize agar bounding box bagus)
        if frame_count % 5 == 0:
            gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)

            # deteksi wajah full quality
            faces = face_cascade.detectMultiScale(
                gray,
                scaleFactor=1.1,
                minNeighbors=5,
                minSize=(80, 80)
            )

            # tambahkan padding agar kotak tidak mepet ke wajah
            padded_faces = []
            for (x, y, w, h) in faces:
                pad = 25   # padding 25px
                x2 = max(0, x - pad)
                y2 = max(0, y - pad)
                w2 = min(frame.shape[1] - x2, w + pad * 2)
                h2 = min(frame.shape[0] - y2, h + pad * 2)
                padded_faces.append((x2, y2, w2, h2))

            last_faces = padded_faces

            # tambah ke queue untuk identifikasi
            if frame_count % 15 == 0:
                with queue_lock:
                    identification_queue.clear()
                    for i, (x, y, w, h) in enumerate(last_faces):
                        face_img = frame[y:y+h, x:x+w]
                        if face_img.size > 0:
                            identification_queue.append((i, face_img))

        # gambar bounding box
        for i, (x, y, w, h) in enumerate(last_faces):
            x, y, w, h = int(x), int(y), int(w), int(h)

            # ambil hasil identifikasi
            with cache_lock:
                if i in identification_cache:
                    cached = identification_cache[i]
                    if time.time() - cached['timestamp'] > 2:
                        name, confidence = "detecting...", 0
                    else:
                        name = cached['name']
                        confidence = cached['confidence']
                else:
                    name, confidence = "detecting...", 0

            # warna tetap sama
            color = (0, 255, 0) if name not in ["unknown", "detecting..."] else (0, 0, 255)

            # gambar kotak
            cv2.rectangle(frame, (x, y), (x+w, y+h), color, 2)

            # label
            label = f"{name}" if name in ["unknown", "detecting..."] else f"{name} ({confidence}%)"
            cv2.rectangle(frame, (x, y - 30), (x + w, y), color, cv2.FILLED)
            cv2.putText(frame, label, (x + 6, y - 8), cv2.FONT_HERSHEY_SIMPLEX, 0.55, (255, 255, 255), 1)

        # encode frame untuk streaming
        ret, buffer = cv2.imencode('.jpg', frame, [cv2.IMWRITE_JPEG_QUALITY, 95])
        frame_bytes = buffer.tobytes()

        yield (b'--frame\r\n'
               b'Content-Type: image/jpeg\r\n\r\n' + frame_bytes + b'\r\n')

    # global last_faces, frame_count
    # init_camera()

    face_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_default.xml')

    while True:
        success, frame = camera.read()
        if not success:
            break

        frame_count += 1

        # deteksi wajah hanya setiap 5 frame
        if frame_count % 5 == 0:
            small_frame = cv2.resize(frame, (0, 0), fx=0.4, fy=0.4)
            gray = cv2.cvtColor(small_frame, cv2.COLOR_BGR2GRAY)
            faces = face_cascade.detectMultiScale(gray, 1.2, 5, minSize=(40, 40))

            # scale koordinat kembali
            last_faces = [(x*2.5, y*2.5, w*2.5, h*2.5) for (x, y, w, h) in faces]

            # tambahkan ke queue untuk identifikasi
            if frame_count % 15 == 0:
                with queue_lock:
                    identification_queue.clear()
                    for i, (x, y, w, h) in enumerate(last_faces):
                        face_img = frame[int(y):int(y+h), int(x):int(x+w)]
                        if face_img.size > 0:
                            identification_queue.append((i, face_img))

        # gambar kotak untuk semua wajah
        for i, (x, y, w, h) in enumerate(last_faces):
            x, y, w, h = int(x), int(y), int(w), int(h)

            # ambil hasil identifikasi dari cache
            with cache_lock:
                if i in identification_cache:
                    cached = identification_cache[i]
                    # hapus cache yang sudah lama (> 2 detik)
                    if time.time() - cached['timestamp'] > 2:
                        name, confidence = "detecting...", 0
                    else:
                        name = cached['name']
                        confidence = cached['confidence']
                else:
                    name, confidence = "detecting...", 0

            color = (0, 255, 0) if name != "unknown" and name != "detecting..." else (0, 0, 255)
            cv2.rectangle(frame, (x, y), (x+w, y+h), color, 2)
            cv2.rectangle(frame, (x, y+h-35), (x+w, y+h), color, cv2.FILLED)

            text = f"{name}" if name in ["unknown", "detecting..."] else f"{name} ({confidence}%)"
            cv2.putText(frame, text, (x+6, y+h-6), cv2.FONT_HERSHEY_SIMPLEX, 0.5, (255, 255, 255), 1)

        # encode dengan kualitas rendah untuk speed
        ret, buffer = cv2.imencode('.jpg', frame, [cv2.IMWRITE_JPEG_QUALITY, 100])
        frame_bytes = buffer.tobytes()

        yield (b'--frame\r\n'
               b'Content-Type: image/jpeg\r\n\r\n' + frame_bytes + b'\r\n')

@app.route('/api/video')
def video():
    return Response(generate_frames(),
                    mimetype='multipart/x-mixed-replace; boundary=frame')

@app.route('/api/detect', methods=['POST'])
def detect_face():
    try:
        if 'image' not in request.files:
            return jsonify({'error': 'tidak ada file gambar'}), 400

        image_file = request.files['image']
        image_bytes = image_file.read()
        nparr = np.frombuffer(image_bytes, np.uint8)
        image = cv2.imdecode(nparr, cv2.IMREAD_COLOR)

        gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
        face_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_default.xml')
        faces = face_cascade.detectMultiScale(gray, 1.1, 4, minSize=(100, 100))

        detected_names = []

        for (x, y, w, h) in faces:
            face_img = image[y:y+h, x:x+w]
            name, confidence = identify_face_sync(face_img)

            detected_names.append({
                'name': name,
                'confidence': confidence,
                'location': {'x': int(x), 'y': int(y), 'w': int(w), 'h': int(h)}
            })

            color = (0, 255, 0) if name != "unknown" else (0, 0, 255)
            cv2.rectangle(image, (x, y), (x+w, y+h), color, 2)
            cv2.rectangle(image, (x, y+h-35), (x+w, y+h), color, cv2.FILLED)

            text = f"{name}" if name == "unknown" else f"{name} ({confidence}%)"
            cv2.putText(image, text, (x+6, y+h-6), cv2.FONT_HERSHEY_DUPLEX, 0.6, (255, 255, 255), 1)

        ret, buffer = cv2.imencode('.jpg', image)
        image_base64 = base64.b64encode(buffer).decode('utf-8')

        return jsonify({
            'success': True,
            'image': f'data:image/jpeg;base64,{image_base64}',
            'faces_detected': len(detected_names),
            'faces': detected_names
        })

    except Exception as e:
        return jsonify({'error': str(e)}), 500

if __name__ == '__main__':
    print("face recognition api (lite version) siap digunakan!")
    print("versi ini menggunakan threading untuk performa lebih baik")
    print("server berjalan di http://127.0.0.1:5000")
    app.run(host='127.0.0.1', port=5000, debug=False, threaded=True)