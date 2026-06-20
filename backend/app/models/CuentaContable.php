<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

/**
 * Modelo CuentaContable — plan de cuentas (PUC).
 * Tabla: cuentas_contables. Historias: CF-CON-001, CF-CON-011.
 */
final class CuentaContable
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function listar(?int $tiendaId = null, bool $soloActivas = false): array
    {
        // tienda_id es NOT NULL en BD: null aquí significa "todas las tiendas" (Superadmin)
        $sql = "
            SELECT c.id, c.tienda_id, c.codigo, c.nombre, c.tipo, c.naturaleza,
                   c.cuenta_padre_id, c.nivel, c.activo,
                   p.nombre AS padre_nombre,
                   t.nombre AS tienda_nombre
            FROM cuentas_contables c
            LEFT JOIN cuentas_contables p ON p.id = c.cuenta_padre_id
            INNER JOIN tiendas t ON t.id = c.tienda_id
            WHERE 1 = 1
        ";

        $parametros = [];

        if ($tiendaId !== null) {
            $sql .= " AND c.tienda_id = :tienda_id";
            $parametros[':tienda_id'] = $tiendaId;
        }

        if ($soloActivas) {
            $sql .= " AND c.activo = 1";
        }

        $sql .= " ORDER BY c.tienda_id ASC, c.codigo ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->fetchAll();
    }

    /** @param string[] $tipos */
    public function listarPorTipo(?int $tiendaId, array $tipos): array
    {
        if ($tipos === []) {
            return [];
        }

        $marcadores = implode(',', array_fill(0, count($tipos), '?'));

        $sql = "
            SELECT cc.id, cc.codigo, cc.nombre, cc.tipo, cc.naturaleza,
                   cc.tienda_id, t.nombre AS tienda_nombre
            FROM cuentas_contables cc
            INNER JOIN tiendas t ON t.id = cc.tienda_id
            WHERE cc.activo = 1 AND cc.tipo IN ($marcadores)
        ";

        $parametros = array_values($tipos);

        if ($tiendaId !== null) {
            $sql .= " AND cc.tienda_id = ?";
            $parametros[] = $tiendaId;
        }

        $sql .= " ORDER BY cc.tienda_id ASC, cc.codigo ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM cuentas_contables WHERE id = :id LIMIT 1
        ");
        $stmt->execute([':id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function existeCodigo(string $codigo, ?int $tiendaId, ?int $excluirId = null): bool
    {
        // La unicidad del código es por tienda (UNIQUE tienda_id+codigo en BD)
        $sql = "SELECT COUNT(*) AS total FROM cuentas_contables WHERE codigo = :codigo";
        $parametros = [':codigo' => trim($codigo)];

        if ($tiendaId !== null) {
            $sql .= " AND tienda_id = :tienda_id";
            $parametros[':tienda_id'] = $tiendaId;
        }

        if ($excluirId !== null) {
            $sql .= " AND id <> :excluir";
            $parametros[':excluir'] = $excluirId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);

        return (int) $stmt->fetch()['total'] > 0;
    }

    public function crear(array $datos): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO cuentas_contables (
                tienda_id, codigo, nombre, tipo, naturaleza,
                cuenta_padre_id, nivel, activo
            ) VALUES (
                :tienda_id, :codigo, :nombre, :tipo, :naturaleza,
                :cuenta_padre_id, :nivel, :activo
            )
        ");
        $stmt->execute([
            ':tienda_id'       => $datos['tienda_id'],
            ':codigo'          => $datos['codigo'],
            ':nombre'          => $datos['nombre'],
            ':tipo'            => $datos['tipo'],
            ':naturaleza'      => $datos['naturaleza'],
            ':cuenta_padre_id' => $datos['cuenta_padre_id'],
            ':nivel'           => $datos['nivel'],
            ':activo'          => $datos['activo'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function actualizar(int $id, array $datos): bool
    {
        $stmt = $this->db->prepare("
            UPDATE cuentas_contables
            SET nombre = :nombre,
                tipo = :tipo,
                naturaleza = :naturaleza,
                cuenta_padre_id = :cuenta_padre_id,
                nivel = :nivel
            WHERE id = :id
        ");

        return $stmt->execute([
            ':id'              => $id,
            ':nombre'          => $datos['nombre'],
            ':tipo'            => $datos['tipo'],
            ':naturaleza'      => $datos['naturaleza'],
            ':cuenta_padre_id' => $datos['cuenta_padre_id'],
            ':nivel'           => $datos['nivel'],
        ]);
    }

    public function cambiarEstado(int $id, int $activo): bool
    {
        $stmt = $this->db->prepare("
            UPDATE cuentas_contables SET activo = :activo WHERE id = :id
        ");

        return $stmt->execute([':id' => $id, ':activo' => $activo]);
    }

    /**
     * Siembra un plan de cuentas básico (PUC colombiano simplificado)
     * para la tienda indicada, de modo que el módulo sea utilizable
     * desde el primer día (CF-CON-001).
     *
     * tienda_id es NOT NULL en la BD, por lo que el PUC se crea por tienda.
     */
    public function asegurarPlanBase(int $tiendaId): void
    {
        if ($tiendaId <= 0) {
            return;
        }

        $cuentas = [
            ['1', 'Activo', 'activo', 'debito', 1],
            ['11', 'Disponible', 'activo', 'debito', 2],
            ['1105', 'Caja', 'activo', 'debito', 3],
            ['1110', 'Bancos', 'activo', 'debito', 3],
            ['13', 'Deudores', 'activo', 'debito', 2],
            ['1305', 'Clientes', 'activo', 'debito', 3],
            ['14', 'Inventarios', 'activo', 'debito', 2],
            ['1435', 'Mercancías no fabricadas', 'activo', 'debito', 3],
            ['2', 'Pasivo', 'pasivo', 'credito', 1],
            ['22', 'Proveedores', 'pasivo', 'credito', 2],
            ['2205', 'Proveedores nacionales', 'pasivo', 'credito', 3],
            ['24', 'Impuestos por pagar', 'pasivo', 'credito', 2],
            ['2408', 'IVA por pagar', 'pasivo', 'credito', 3],
            ['25', 'Obligaciones laborales', 'pasivo', 'credito', 2],
            ['2505', 'Salarios por pagar', 'pasivo', 'credito', 3],
            ['3', 'Patrimonio', 'patrimonio', 'credito', 1],
            ['3105', 'Capital', 'patrimonio', 'credito', 2],
            ['4', 'Ingresos', 'ingreso', 'credito', 1],
            ['4135', 'Comercio al por mayor y menor', 'ingreso', 'credito', 2],
            ['5', 'Gastos', 'egreso', 'debito', 1],
            ['5105', 'Gastos de personal', 'egreso', 'debito', 2],
            ['5135', 'Servicios', 'egreso', 'debito', 2],
            ['5195', 'Diversos', 'egreso', 'debito', 2],
            ['6', 'Costo de ventas', 'costo', 'debito', 1],
            ['6135', 'Costo de mercancía vendida', 'costo', 'debito', 2],
        ];

        foreach ($cuentas as [$codigo, $nombre, $tipo, $naturaleza, $nivel]) {
            if (!$this->existeCodigo($codigo, $tiendaId)) {
                $this->crear([
                    'tienda_id'       => $tiendaId,
                    'codigo'          => $codigo,
                    'nombre'          => $nombre,
                    'tipo'            => $tipo,
                    'naturaleza'      => $naturaleza,
                    'cuenta_padre_id' => null,
                    'nivel'           => $nivel,
                    'activo'          => 1,
                ]);
            }
        }
    }
}
