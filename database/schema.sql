CREATE TABLE sensor_realtime (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  suhu DOUBLE,
  kelembapan_tanah INT,
  ph DOUBLE,
  relay TINYINT
);

CREATE TABLE sensor_hourly (
  hour_start DATETIME NOT NULL,
  suhu_avg DOUBLE,
  kelembapan_avg DOUBLE,
  ph_avg DOUBLE,
  PRIMARY KEY (hour_start)
);

CREATE TABLE pump_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  started_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  duration_sec INT,
  reason VARCHAR(20),
  action VARCHAR(10),
  note VARCHAR(255)
);

CREATE TABLE moisture_config (
  id TINYINT PRIMARY KEY,
  enabled TINYINT NOT NULL,
  threshold INT NOT NULL,
  target INT NOT NULL
);

INSERT INTO moisture_config (id, enabled, threshold, target)
VALUES (1, 0, 30, 70);
