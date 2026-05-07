<?php if (!empty($itemsCarritoDetallado)): ?>
    <?php foreach ($itemsCarritoDetallado as $item): ?>
        <tr>
            <td><?php echo htmlspecialchars((string)$item['nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo (int)$item['cantidad']; ?></td>
            <td>Bs. <?php echo number_format((float)$item['precio'], 2); ?></td>
            <td>Bs. <?php echo number_format((float)$item['subtotal'], 2); ?></td>
            <td>
                <div class="d-flex gap-2">
                    <input type="number" id="cantidad_<?php echo (int)$item['id_producto']; ?>" min="0" value="<?php echo (int)$item['cantidad']; ?>" class="form-control form-control-sm" style="width: 70px;">
                    <button onclick="actualizarCantidad(<?php echo (int)$item['id_producto']; ?>, document.getElementById('cantidad_<?php echo (int)$item['id_producto']; ?>').value)" class="btn btn-primary btn-sm">Actualizar</button>
                    <button onclick="eliminarDelCarrito(<?php echo (int)$item['id_producto']; ?>)" class="btn btn-danger btn-sm">Eliminar</button>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="5" class="text-center">El carrito esta vacio.</td>
    </tr>
<?php endif; ?>
