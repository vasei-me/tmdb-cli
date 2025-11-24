$token = "eyJhb6ci0iJUz11NJ9.eyJhdWQi0i0O61MjNiMWJmZmU1NTA0M2Q4ZmMwNDcyYzQoZWMsYylsIm5iZli6MTc2MzU40DU0MC43MzEslnN17lI6jY5MWUzOWJjNjZIMmNmMWQxOWFtYjcsNSlslnNjbsBicy16WyJhcGlfcmvhZCJdtCJ2ZXJzaW9ujpoxfQ.m_KAXC8OHYT6kC0Ks86CrFAI3yRFrSJiyQo63vb5ZYQ"; 
$url = "https://api.themoviedb.org/3/movie/popular"; 
$options = ['http' =, 'header' =, 'timeout' =
$context = stream_context_create($options); 
$response = @file_get_contents($url, false, $context); 
if ($response === false) { echo "❌ Connection failed\n"; $error = error_get_last(); echo "Error: " . $error['message'] . "\n"; } else { $data = json_decode($response, true); if (isset($data['results'])) { echo "✅ Token works! Found " . count($data['results']) . " movies\n"; echo "First movie: " . $data['results'][0]['title'] . "\n"; } else { echo "❌ Token error:\n"; echo "Status: " . ($data['status_code'] ?? 'Unknown') . "\n"; echo "Message: " . ($data['status_message'] ?? 'Unknown error') . "\n"; } } 
$token = "eyJhb6ci0iJUz11NJ9.eyJhdWQi0i0O61MjNiMWJmZmU1NTA0M2Q4ZmMwNDcyYzQoZWMsYylsIm5iZli6MTc2MzU40DU0MC43MzEslnN17lI6jY5MWUzOWJjNjZIMmNmMWQxOWFtYjcsNSlslnNjbsBicy16WyJhcGlfcmvhZCJdtCJ2ZXJzaW9ujpoxfQ.m_KAXC8OHYT6kC0Ks86CrFAI3yRFrSJiyQo63vb5ZYQ"; 
$url = "https://api.themoviedb.org/3/movie/popular"; 
$options = ['http' =, 'header' =, 'timeout' =
$context = stream_context_create($options); 
$response = @file_get_contents($url, false, $context); 
if ($response === false) { echo "❌ Connection failed\n"; $error = error_get_last(); echo "Error: " . $error['message'] . "\n"; } else { $data = json_decode($response, true); if (isset($data['results'])) { echo "✅ Token works! Found " . count($data['results']) . " movies\n"; echo "First movie: " . $data['results'][0]['title'] . "\n"; } else { echo "❌ Token error:\n"; echo "Status: " . ($data['status_code'] ?? 'Unknown') . "\n"; echo "Message: " . ($data['status_message'] ?? 'Unknown error') . "\n"; } } 
$token = "eyJhb6ci0iJUz11NJ9.eyJhdWQi0i0O61MjNiMWJmZmU1NTA0M2Q4ZmMwNDcyYzQoZWMsYylsIm5iZli6MTc2MzU40DU0MC43MzEslnN17lI6jY5MWUzOWJjNjZIMmNmMWQxOWFtYjcsNSlslnNjbsBicy16WyJhcGlfcmvhZCJdtCJ2ZXJzaW9ujpoxfQ.m_KAXC8OHYT6kC0Ks86CrFAI3yRFrSJiyQo63vb5ZYQ"; 
$url = "https://api.themoviedb.org/3/movie/popular"; 
$options = ['http' =, 'header' =, 'timeout' =
$context = stream_context_create($options); 
$response = @file_get_contents($url, false, $context); 
if ($response === false) { echo "❌ Connection failed\n"; $error = error_get_last(); echo "Error: " . $error['message'] . "\n"; } else { $data = json_decode($response, true); if (isset($data['results'])) { echo "✅ Token works! Found " . count($data['results']) . " movies\n"; echo "First movie: " . $data['results'][0]['title'] . "\n"; } else { echo "❌ Token error:\n"; echo "Status: " . ($data['status_code'] ?? 'Unknown') . "\n"; echo "Message: " . ($data['status_message'] ?? 'Unknown error') . "\n"; } } 
