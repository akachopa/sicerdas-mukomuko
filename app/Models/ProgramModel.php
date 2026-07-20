<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class ProgramModel
{
    /** Subquery nilai komitmen aktif sebuah program. */
    public const COMMITTED_SQL =
        "(SELECT COALESCE(SUM(c.amount),0) FROM commitments c
          WHERE c.program_id = p.id AND c.status IN ('disetujui','aktif','direalisasikan_sebagian','direalisasikan_penuh','selesai'))";

    /** Subquery nilai realisasi terverifikasi sebuah program. */
    public const REALIZED_SQL =
        "(SELECT COALESCE(SUM(rz.amount),0) FROM realizations rz
          JOIN commitments c2 ON c2.id = rz.commitment_id
          WHERE c2.program_id = p.id AND rz.status = 'terverifikasi')";

    /** @return array{committed: float, realized: float, gap: float} */
    public static function funding(int $programId): array
    {
        $row = Database::selectOne(
            "SELECT p.budget_needed,
                    " . self::COMMITTED_SQL . " AS committed,
                    " . self::REALIZED_SQL . " AS realized
             FROM programs p WHERE p.id = ?",
            [$programId]
        ) ?? ['budget_needed' => 0, 'committed' => 0, 'realized' => 0];

        return [
            'committed' => (float) $row['committed'],
            'realized' => (float) $row['realized'],
            'gap' => max(0.0, (float) $row['budget_needed'] - (float) $row['committed']),
        ];
    }

    /** Sinkronkan status pendanaan program berdasarkan total komitmen aktif. */
    public static function refreshFundingStatus(int $programId): void
    {
        $program = Database::selectOne("SELECT budget_needed, status FROM programs WHERE id = ?", [$programId]);
        if ($program === null) {
            return;
        }
        // Hanya perbarui status ketika program sudah pada fase pendanaan
        if (!in_array($program['status'], ['dipublikasikan', 'dalam_penjajakan', 'komitmen_sebagian', 'komitmen_penuh'], true)) {
            return;
        }
        $funding = self::funding($programId);
        $newStatus = $funding['committed'] <= 0
            ? 'dipublikasikan'
            : ($funding['gap'] > 0 ? 'komitmen_sebagian' : 'komitmen_penuh');
        Database::update('programs', ['status' => $newStatus], 'id = ?', [$programId]);
    }

    public static function generateCode(): string
    {
        $year = date('Y');
        $last = (int) Database::scalar(
            "SELECT COUNT(*) FROM programs WHERE code LIKE ?",
            ["PRG-$year-%"]
        );
        return sprintf('PRG-%s-%03d', $year, $last + 1);
    }
}
