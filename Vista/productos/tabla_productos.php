<?php
if (!function_exists('esc')) {
    function esc(string $valor): string
    {
        return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
    }
}
?>

<?php if (!empty($productos)): ?>
    <?php foreach ($productos as $producto): 
        $stock = (int)$producto['stock'];
        $stock_class = $stock > 5 ? 'stock-alto' : ($stock > 1 ? 'stock-medio' : 'stock-bajo');
    ?>
        <tr>
            <td><?php echo (int)$producto['id_producto']; ?></td>
            <td><strong><?php echo esc((string)$producto['nombre']); ?></strong></td>
            <td><?php echo esc((string)$producto['descripcion']); ?></td>
            <td>
                <span class="price-badge">Bs. <?php echo number_format((float)$producto['precio'], 2); ?></span>
            </td>
            <td>
                <span class="stock-badge <?php echo $stock_class; ?>">
                    <?php echo $stock; ?> 
                    <?php echo $stock === 1 ? 'unidad' : 'unidades'; ?>
                </span>
            </td>
            <td>
                <div class="action-buttons">
                    <a href="editar_producto.php?id=<?php echo (int)$producto['id_producto']; ?>" class="btn-edit"><i class="fas fa-edit"></i> Editar</a>
                    <a href="gestion_productos.php?accion=eliminar&id=<?php echo (int)$producto['id_producto']; ?>" class="btn-delete" onclick="return confirm('¿Estás seguro de que quieres eliminar este producto?')"><i class="fas fa-trash-alt"></i> Eliminar</a>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="6" style="text-align: center; padding: 40px; color: #6b7280;"><i class="fas fa-inbox"></i> No hay productos disponibles</td>
    </tr>
<?php endif; ?>
