prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
    $stmt->execute(['Denis Calle', 'dennis@mail.com', $password_profesor, 'profesor']);
    
    echo "¡Usuario profesor creado con éxito en la nube de Render!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>