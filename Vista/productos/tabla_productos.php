<?php
if (!function_exists('esc')) {
    function esc(string $valor): string
    {
        return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
    }
}
?>

<?php if (!empty($productos)): ?>
    <?php foreach ($productos as $producto): ?>
        <tr>
            <td><?php echo (int)$producto['id_producto']; ?></td>
            <td><?php echo esc((string)$producto['nombre']); ?></td>
            <td><?php echo esc((string)$producto['descripcion']); ?></td>
            <td>$<?php echo number_format((float)$producto['precio'], 2); ?></td>
            <td><?php echo (int)$producto['stock']; ?></td>
            <td>
                <a href="editar_producto.php?id=<?php echo (int)$producto['id_producto']; ?>" class="btn btn-warning btn-sm">Editar</a>
                <a href="gestion_productos.php?accion=eliminar&id=<?php echo (int)$producto['id_producto']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estas seguro de que quieres eliminar este producto?')">Eliminar</a>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="6" class="text-center">No hay productos disponibles.</td>
    </tr>
<?php endif; ?>
