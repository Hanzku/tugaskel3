<?php
declare(strict_types=1);
require __DIR__ . '/../includes/validation_guru.php';
$checks = 0;
function check(bool $condition, string $message): void
{
    global $checks;
    ++$checks;
    if (!$condition) {
        throw new RuntimeException($message);
    }
}
$valid = ['id_guru' => ' test-01 ', 'nama_guru' => ' Guru Uji '];
[$data, $errors] = validate_guru($valid);
check($errors === [], 'Minimal valid input rejected');
check($data['id_guru'] === 'TEST-01', 'ID normalization failed');
check($data['nama_guru'] === 'Guru Uji', 'Name trimming failed');
foreach (guru_fields() as $key => [$label, $limit]) {
    [, $errors] = validate_guru(array_replace($valid, [$key => []]));
    check(isset($errors[$key]), 'Array accepted: ' . $key);
    [, $errors] = validate_guru(array_replace($valid, [$key => str_repeat('a', $limit + 1)]));
    check(isset($errors[$key]), 'Overlength accepted: ' . $key);
    [, $errors] = validate_guru(array_replace($valid, [$key => "abc\x00"]));
    check(isset($errors[$key]), 'Control character accepted: ' . $key);
    [, $errors] = validate_guru(array_replace($valid, [$key => "\xFF"]));
    check(isset($errors[$key]), 'Invalid UTF-8 accepted: ' . $key);
}
foreach (['id_guru' => ['', 'A B'], 'nama_guru' => ['', '   '], 'email' => ['invalid', 'a@'], 'telepon' => ['123', 'letters', '1234567890123456'], 'jenis_kelamin' => ['X'], 'nip' => ['123'], 'nuptk' => ['123']] as $key => $values) {
    foreach ($values as $value) {
        [, $errors] = validate_guru(array_replace($valid, [$key => $value]));
        check(isset($errors[$key]), 'Invalid value accepted: ' . $key);
    }
}
[, $errors] = validate_guru(array_replace($valid, ['nip' => str_repeat('0', 18), 'nuptk' => str_repeat('0', 16), 'email' => 'test@example.invalid', 'telepon' => '+62 (812) 345-6789', 'jenis_kelamin' => 'L', 'kd_pelajaran' => 'TEST-01']));
check($errors === [], 'Valid optional fields rejected');
[, $errors] = validate_guru(array_replace($valid, ['nama_guru' => str_repeat('a', 100)]));
check($errors === [], 'Boundary name rejected');
echo "PASS: $checks guru validation checks\n";