DELETE FROM sensor_realtime;
INSERT INTO sensor_realtime (ts, suhu, kelembapan_tanah, ph, relay) VALUES
  (NOW() - INTERVAL 5 HOUR, 26.1, 55, 6.5, 0),
  (NOW() - INTERVAL 4 HOUR 30 MINUTE, 26.0, 54, 6.5, 0),
  (NOW() - INTERVAL 4 HOUR, 25.8, 53, 6.5, 0),
  (NOW() - INTERVAL 3 HOUR 30 MINUTE, 25.7, 52, 6.6, 0),
  (NOW() - INTERVAL 3 HOUR, 25.6, 50, 6.6, 0),
  (NOW() - INTERVAL 2 HOUR 30 MINUTE, 25.5, 49, 6.6, 0),
  (NOW() - INTERVAL 2 HOUR, 25.5, 48, 6.6, 0),
  (NOW() - INTERVAL 1 HOUR 30 MINUTE, 25.7, 47, 6.6, 0),
  (NOW() - INTERVAL 1 HOUR, 26.0, 46, 6.7, 0),
  (NOW() - INTERVAL 30 MINUTE, 26.3, 45, 6.7, 0),
  (NOW(), 26.5, 44, 6.7, 0);
