<?php

namespace App\Core;

abstract class Controller {
    protected function render(string $view, array $data = [], ?string $layout = 'main'): void {
        view($view, $data, $layout);
    }

    protected function json(mixed $data, int $status = 200): void {
        (new Response())->json($data, $status);
    }

    protected function redirect(string $url, int $status = 302): void {
        (new Response())->redirect($url, $status);
    }

    protected function validate(array $data, array $rules): ?Validator {
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            Session::setOldInput($data);
            Session::setFlash('error', $validator->firstError() ?? 'Please correct the validation errors.');
            return $validator;
        }
        return null;
    }

    protected function flash(string $key, string $message): void {
        Session::setFlash($key, $message);
    }
}
