<?php if (!empty($itemsCarritoDetallado)): ?>
    <?php foreach ($itemsCarritoDetallado as $item): ?>
        <tr>
            <td><?php echo htmlspecialchars((string)$item['nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo (int)$item['cantidad']; ?></td>
            <td>$<?php echo number_format((float)$item['precio'], 2); ?></td>
            <td>$<?php echo number_format((float)$item['subtotal'], 2); ?></td>
            <td>
                <form action="carrito.php" method="GET" class="d-inline-flex align-items-center mb-1 mr-1">
                    <input type="hidden" name="accion" value="actualizar">
                    <input type="hidden" name="id" value="<?php echo (int)$item['id_producto']; ?>">
                    <input type="number" name="cantidad" min="0" value="<?php echo (int)$item['cantidad']; ?>" class="form-control form-control-sm mr-1" style="width: 78px;">
                    <button type="submit" class="btn btn-primary btn-sm">Actualizar</button>
                </form>
                <a href="carrito.php?accion=eliminar&id=<?php echo (int)$item['id_producto']; ?>" class="btn btn-danger btn-sm mb-1">Eliminar</a>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="5" class="text-center">El carrito esta vacio.</td>
    </tr>
<?php endif; ?>
