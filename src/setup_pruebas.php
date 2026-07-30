<?php
// setup_pruebas.php
require 'config.php';
$pdo = getDBConnection();

$temas = [
    [
        'titulo' => '1. Present Simple',
        'descripcion' => 'Evaluación de rutinas, hábitos y verdades generales.',
        'preguntas' => [
            ['texto' => 'She ___ to the gym every morning.', 'opciones' => ['go', 'goes', 'going', 'is go'], 'correcta' => 1],
            ['texto' => 'The sun rises in the west. (True or False)', 'opciones' => ['True', 'False'], 'correcta' => 1],
            ['texto' => 'I ___ (not/like) spicy food.', 'opciones' => ['not like', 'don\'t like', 'doesn\'t like', 'am not like'], 'correcta' => 1],
            ['texto' => '___ they play tennis on weekends?', 'opciones' => ['Do', 'Does', 'Are', 'Is'], 'correcta' => 0],
            ['texto' => 'Water boils at 100 degrees Celsius.', 'opciones' => ['True', 'False'], 'correcta' => 0],
            ['texto' => 'My brother never ___ his room.', 'opciones' => ['clean', 'cleans', 'cleaning', 'is clean'], 'correcta' => 1],
            ['texto' => 'Where ___ you live?', 'opciones' => ['are', 'is', 'does', 'do'], 'correcta' => 3],
            ['texto' => 'Which sentence is CORRECT?', 'opciones' => ['He don\'t works here.', 'He doesn\'t work here.', 'He doesn\'t works here.', 'He not work here.'], 'correcta' => 1],
            ['texto' => 'Cats ___ milk.', 'opciones' => ['drinks', 'drink', 'drinking', 'are drink'], 'correcta' => 1],
            ['texto' => 'The train ___ (leave) at 8:00 PM.', 'opciones' => ['leave', 'leaves', 'leaving', 'is leave'], 'correcta' => 1]
        ]
    ],
    [
        'titulo' => '2. Present Continuous',
        'descripcion' => 'Evaluación de acciones que están ocurriendo en este momento.',
        'preguntas' => [
            ['texto' => 'Listen! The baby ___.', 'opciones' => ['cry', 'cries', 'is crying', 'are crying'], 'correcta' => 2],
            ['texto' => 'I can\'t talk right now, I ___ my homework.', 'opciones' => ['do', 'am doing', 'does', 'doing'], 'correcta' => 1],
            ['texto' => 'Are they ___ TV at the moment?', 'opciones' => ['watch', 'watches', 'watching', 'watched'], 'correcta' => 2],
            ['texto' => 'She is not ___ to music.', 'opciones' => ['listen', 'listens', 'listening', 'listened'], 'correcta' => 2],
            ['texto' => 'Which sentence is CORRECT?', 'opciones' => ['He is runing fast.', 'He is running fast.'], 'correcta' => 1],
            ['texto' => 'Look! It ___ outside.', 'opciones' => ['rains', 'is raining', 'rain', 'raining'], 'correcta' => 1],
            ['texto' => 'What ___ you doing?', 'opciones' => ['do', 'does', 'are', 'is'], 'correcta' => 2],
            ['texto' => 'We ___ (have) dinner right now.', 'opciones' => ['have', 'has', 'are having', 'is having'], 'correcta' => 2],
            ['texto' => 'The dog ___ chasing the cat.', 'opciones' => ['am', 'is', 'are', 'do'], 'correcta' => 1],
            ['texto' => '___ she working today?', 'opciones' => ['Do', 'Does', 'Is', 'Are'], 'correcta' => 2]
        ]
    ],
    [
        'titulo' => '3. Present Perfect Simple',
        'descripcion' => 'Acciones pasadas con relevancia en el presente o experiencias de vida.',
        'preguntas' => [
            ['texto' => 'I ___ (visit) Paris three times.', 'opciones' => ['have visit', 'has visited', 'have visited', 'visited'], 'correcta' => 2],
            ['texto' => '___ you ever eaten sushi?', 'opciones' => ['Has', 'Have', 'Do', 'Did'], 'correcta' => 1],
            ['texto' => 'She hasn\'t ___ her homework yet.', 'opciones' => ['finish', 'finishes', 'finished', 'finishing'], 'correcta' => 2],
            ['texto' => 'We have lived here ___ 2010.', 'opciones' => ['for', 'since', 'in', 'at'], 'correcta' => 1],
            ['texto' => 'They have ___ arrived.', 'opciones' => ['just', 'yet', 'ever', 'since'], 'correcta' => 0],
            ['texto' => 'He ___ (lose) his keys.', 'opciones' => ['has lose', 'have lost', 'has lost', 'lost'], 'correcta' => 2],
            ['texto' => '"I have went to the store." (True or False)', 'opciones' => ['True', 'False (It should be: have gone)'], 'correcta' => 1],
            ['texto' => 'How long ___ you known him?', 'opciones' => ['has', 'have', 'do', 'are'], 'correcta' => 1],
            ['texto' => 'She has ___ to Japan.', 'opciones' => ['be', 'been', 'was', 'went'], 'correcta' => 1],
            ['texto' => 'I haven\'t ___ that movie.', 'opciones' => ['saw', 'see', 'seeing', 'seen'], 'correcta' => 3]
        ]
    ],
    [
        'titulo' => '4. Present Perfect Continuous',
        'descripcion' => 'Acciones que comenzaron en el pasado y continúan en el presente.',
        'preguntas' => [
            ['texto' => 'I have been ___ for three hours.', 'opciones' => ['study', 'studied', 'studying', 'studies'], 'correcta' => 2],
            ['texto' => 'How long have you been ___ ?', 'opciones' => ['wait', 'waiting', 'waited', 'waits'], 'correcta' => 1],
            ['texto' => 'She ___ been working here since Monday.', 'opciones' => ['have', 'has', 'is', 'was'], 'correcta' => 1],
            ['texto' => 'It has been ___ all day.', 'opciones' => ['rain', 'raining', 'rained', 'rains'], 'correcta' => 1],
            ['texto' => 'They haven\'t been ___ well lately.', 'opciones' => ['sleep', 'sleeping', 'slept', 'sleeps'], 'correcta' => 1],
            ['texto' => '___ you been exercising?', 'opciones' => ['Has', 'Have', 'Are', 'Do'], 'correcta' => 1],
            ['texto' => 'He has been ___ that book for weeks.', 'opciones' => ['read', 'reading', 'reads', 'readed'], 'correcta' => 1],
            ['texto' => 'We use this tense for completed past actions. (True or False)', 'opciones' => ['True', 'False'], 'correcta' => 1],
            ['texto' => 'I\'m tired because I have been ___.', 'opciones' => ['run', 'ran', 'running', 'runs'], 'correcta' => 2],
            ['texto' => 'She has been ___ to call you.', 'opciones' => ['try', 'trying', 'tried', 'tries'], 'correcta' => 1]
        ]
    ],
    [
        'titulo' => '5. Past Simple',
        'descripcion' => 'Acciones completadas en un momento específico del pasado.',
        'preguntas' => [
            ['texto' => 'I ___ to the cinema yesterday.', 'opciones' => ['go', 'gone', 'went', 'was'], 'correcta' => 2],
            ['texto' => 'She ___ like the food.', 'opciones' => ['didn\'t', 'don\'t', 'doesn\'t', 'wasn\'t'], 'correcta' => 0],
            ['texto' => '___ you see the match last night?', 'opciones' => ['Do', 'Did', 'Were', 'Have'], 'correcta' => 1],
            ['texto' => 'We ___ tennis last weekend.', 'opciones' => ['play', 'played', 'playing', 'plays'], 'correcta' => 1],
            ['texto' => 'He ___ very happy yesterday.', 'opciones' => ['were', 'is', 'was', 'be'], 'correcta' => 2],
            ['texto' => 'They ___ at the party.', 'opciones' => ['was', 'were', 'been', 'are'], 'correcta' => 1],
            ['texto' => 'The past tense of "buy" is "buyed". (True or False)', 'opciones' => ['True', 'False (It is bought)'], 'correcta' => 1],
            ['texto' => 'I ___ a new car last month.', 'opciones' => ['buy', 'bought', 'buyed', 'buying'], 'correcta' => 1],
            ['texto' => 'What ___ you do yesterday?', 'opciones' => ['do', 'does', 'did', 'done'], 'correcta' => 2],
            ['texto' => 'She ___ early.', 'opciones' => ['leave', 'left', 'leaved', 'leaving'], 'correcta' => 1]
        ]
    ],
    [
        'titulo' => '6. Past Continuous',
        'descripcion' => 'Acciones que estaban en progreso en un momento del pasado.',
        'preguntas' => [
            ['texto' => 'I ___ TV when the phone rang.', 'opciones' => ['watched', 'was watching', 'were watching', 'watch'], 'correcta' => 1],
            ['texto' => 'They ___ football at 5 PM yesterday.', 'opciones' => ['was playing', 'were playing', 'played', 'play'], 'correcta' => 1],
            ['texto' => 'She ___ listening to the teacher.', 'opciones' => ['wasn\'t', 'weren\'t', 'didn\'t', 'doesn\'t'], 'correcta' => 0],
            ['texto' => '___ you sleeping when I called?', 'opciones' => ['Was', 'Were', 'Did', 'Are'], 'correcta' => 1],
            ['texto' => 'It ___ when we left the house.', 'opciones' => ['was raining', 'were raining', 'rained', 'rains'], 'correcta' => 0],
            ['texto' => 'What ___ you doing at 8 PM?', 'opciones' => ['was', 'were', 'did', 'are'], 'correcta' => 1],
            ['texto' => 'We ___ in the park when it started to rain.', 'opciones' => ['was walking', 'were walking', 'walked', 'walk'], 'correcta' => 1],
            ['texto' => '"He was study" is correct. (True or False)', 'opciones' => ['True', 'False (It should be: was studying)'], 'correcta' => 1],
            ['texto' => 'The kids ___ a lot of noise.', 'opciones' => ['was making', 'were making', 'made', 'make'], 'correcta' => 1],
            ['texto' => 'I was reading a book while she ___ cooking.', 'opciones' => ['were', 'was', 'is', 'did'], 'correcta' => 1]
        ]
    ]
];

try {
    $pdo->beginTransaction(); 

    foreach ($temas as $tema) {
        $tiempo = 15;
        $stmtPrueba = $pdo->prepare("INSERT INTO pruebas (titulo, descripcion, tiempo_minutos) VALUES (?, ?, ?) RETURNING id");
        $stmtPrueba->execute([$tema['titulo'], $tema['descripcion'], $tiempo]);
        $prueba_id = $stmtPrueba->fetchColumn();

        foreach ($tema['preguntas'] as $preg) {
            // CORRECCIÓN 1: Usamos 'texto_pregunta'
            $stmtPreg = $pdo->prepare("INSERT INTO preguntas (prueba_id, texto_pregunta) VALUES (?, ?) RETURNING id");
            $stmtPreg->execute([$prueba_id, $preg['texto']]);
            $pregunta_id = $stmtPreg->fetchColumn();

            foreach ($preg['opciones'] as $index => $texto_opcion) {
                // En PostgreSQL, 1 y 0 se convierten a boolean automáticamente en PDO
                $es_correcta = ($index === $preg['correcta']) ? 1 : 0; 
                // CORRECCIÓN 2: Usamos 'texto_opcion'
                $stmtOpc = $pdo->prepare("INSERT INTO opciones (pregunta_id, texto_opcion, es_correcta) VALUES (?, ?, ?)");
                $stmtOpc->execute([$pregunta_id, $texto_opcion, $es_correcta]);
            }
        }
    }
    
    $pdo->commit();
    echo "¡Pruebas (1 al 6), preguntas y opciones creadas exitosamente!";

} catch (PDOException $e) {
    $pdo->rollBack();
    echo "Error en la base de datos: " . $e->getMessage();
}
?>