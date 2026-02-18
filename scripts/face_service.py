from flask import Flask, request, jsonify
import cv2
import os
import mysql.connector
from datetime import datetime
import numpy as np

app = Flask(__name__)
BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
DATASET_DIR = os.path.join(BASE_DIR, "dataset")

DB_CONFIG = {
    "host": "localhost",
    "user": "root",
    "password": "",
    "database": "smart_attendance"
}

face_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + "haarcascade_frontalface_default.xml")


def get_db():
    return mysql.connector.connect(**DB_CONFIG)


def train_model():
    faces, labels = [], []
    label_map = {}
    current_label = 0

    if not os.path.exists(DATASET_DIR):
        return None, {}

    for student_folder in os.listdir(DATASET_DIR):
        folder_path = os.path.join(DATASET_DIR, student_folder)
        if not os.path.isdir(folder_path):
            continue

        label_map[current_label] = student_folder
        for image_name in os.listdir(folder_path):
            image_path = os.path.join(folder_path, image_name)
            img = cv2.imread(image_path, cv2.IMREAD_GRAYSCALE)
            if img is None:
                continue
            faces.append(img)
            labels.append(current_label)
        current_label += 1

    if not faces:
        return None, {}

    model = cv2.face.LBPHFaceRecognizer_create()
    model.train(faces, np.array(labels))
    return model, label_map


@app.route('/api/capture_dataset', methods=['POST'])
def capture_dataset():
    data = request.json
    student_id = data.get('student_id')
    if not student_id:
        return jsonify({'error': 'student_id is required'}), 400

    student_dir = os.path.join(DATASET_DIR, student_id)
    os.makedirs(student_dir, exist_ok=True)

    cam = cv2.VideoCapture(0)
    count = 0
    while count < 20:
        ret, frame = cam.read()
        if not ret:
            continue
        gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
        faces = face_cascade.detectMultiScale(gray, 1.3, 5)
        for (x, y, w, h) in faces:
            face_img = gray[y:y+h, x:x+w]
            img_path = os.path.join(student_dir, f"{count+1}.jpg")
            cv2.imwrite(img_path, face_img)
            count += 1
            cv2.rectangle(frame, (x, y), (x+w, y+h), (0, 255, 0), 2)
            cv2.putText(frame, f"Captured {count}/20", (10, 30), cv2.FONT_HERSHEY_SIMPLEX, 1, (0, 255, 0), 2)
            if count >= 20:
                break

        cv2.imshow("Capture Faces - Press q to cancel", frame)
        if cv2.waitKey(1) & 0xFF == ord('q'):
            break

    cam.release()
    cv2.destroyAllWindows()

    return jsonify({'message': f'Captured {count} images for {student_id}', 'student_id': student_id, 'images': count})


@app.route('/api/mark_attendance', methods=['POST'])
def mark_attendance():
    payload = request.json
    subject_id = payload.get('subject_id')
    period_label = payload.get('period_label', 'Period')
    staff_id = payload.get('staff_id')

    if not subject_id or not staff_id:
        return jsonify({'error': 'subject_id and staff_id are required'}), 400

    model, label_map = train_model()
    if model is None:
        return jsonify({'error': 'No dataset found. Capture faces first.'}), 400

    db = get_db()
    cursor = db.cursor(dictionary=True)

    now = datetime.now()
    cursor.execute(
        "INSERT INTO attendance_sessions(subject_id, staff_id, period_label, attendance_date, started_at) VALUES(%s, %s, %s, %s, %s)",
        (subject_id, staff_id, period_label, now.date(), now)
    )
    session_id = cursor.lastrowid

    cursor.execute("SELECT id, student_id FROM students")
    students = cursor.fetchall()
    id_by_roll = {s['student_id']: s['id'] for s in students}
    recognized = set()

    cam = cv2.VideoCapture(0)
    frames = 0
    while frames < 300:
        ret, frame = cam.read()
        if not ret:
            continue
        gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
        faces = face_cascade.detectMultiScale(gray, 1.3, 5)
        for (x, y, w, h) in faces:
            roi = gray[y:y+h, x:x+w]
            label, confidence = model.predict(roi)
            if confidence < 80 and label in label_map:
                roll = label_map[label]
                recognized.add(roll)
                cv2.putText(frame, f"{roll}", (x, y-10), cv2.FONT_HERSHEY_SIMPLEX, 0.8, (0, 255, 0), 2)
            cv2.rectangle(frame, (x, y), (x+w, y+h), (255, 0, 0), 2)

        cv2.imshow("Attendance Recognition - Press q to finish", frame)
        frames += 1
        if cv2.waitKey(1) & 0xFF == ord('q'):
            break

    cam.release()
    cv2.destroyAllWindows()

    for roll, student_db_id in id_by_roll.items():
        status = 'present' if roll in recognized else 'absent'
        marked_at = datetime.now() if status == 'present' else None
        cursor.execute(
            "INSERT INTO attendance_records(attendance_session_id, student_id, status, marked_at) VALUES(%s, %s, %s, %s)",
            (session_id, student_db_id, status, marked_at)
        )

    cursor.execute("UPDATE attendance_sessions SET ended_at=%s WHERE id=%s", (datetime.now(), session_id))
    db.commit()
    cursor.close()
    db.close()

    return jsonify({'message': 'Attendance marked successfully', 'session_id': session_id, 'recognized_students': sorted(list(recognized))})


if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5001, debug=True)
