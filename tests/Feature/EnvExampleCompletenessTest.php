<?php

it('env example covers all project env() keys referenced in config/', function () {
    $script = base_path('scripts/check-env-example.sh');

    expect(file_exists($script))->toBeTrue('check-env-example.sh missing from scripts/');

    exec("bash {$script} 2>&1", $output, $exitCode);

    expect($exitCode)
        ->toBe(0, implode("\n", $output));
});
