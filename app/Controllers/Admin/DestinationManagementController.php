<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\AuditLog;
use App\Models\Destination;

class DestinationManagementController extends Controller {
    public function index(Request $request, Response $response): void {
        $destModel = new Destination();
        $this->render('admin/destinations/index', [
            'title' => 'Destinations Management',
            'destinations' => $destModel->all('name ASC')
        ], 'admin');
    }

    public function create(Request $request, Response $response): void {
        $this->render('admin/destinations/form', [
            'title' => 'Create Destination',
            'destination' => []
        ], 'admin');
    }

    public function store(Request $request, Response $response): void {
        $data = $request->getAllPost();
        $rules = [
            'name' => 'required|min:2',
            'slug' => 'required|unique:destinations,slug',
            'country' => 'required',
        ];

        $errors = $this->validate($data, $rules);
        if ($errors) {
            $this->redirect('/admin/destinations/create');
        }

        $destModel = new Destination();
        $id = $destModel->create([
            'name' => trim($data['name']),
            'slug' => strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['slug']))),
            'country' => trim($data['country']),
            'state' => trim($data['state'] ?? ''),
            'city' => trim($data['city'] ?? ''),
            'description' => trim($data['description'] ?? ''),
            'image_url' => trim($data['image_url'] ?? ''),
            'is_featured' => !empty($data['is_featured']) ? 1 : 0,
            'status' => $data['status'] ?? 'active',
        ]);

        AuditLog::record('destination_created', 'destination', $id);
        $this->flash('success', 'Destination created successfully.');
        $this->redirect('/admin/destinations');
    }

    public function edit(Request $request, Response $response, string $id): void {
        $destModel = new Destination();
        $destination = $destModel->find((int)$id);

        if (!$destination) {
            $this->flash('error', 'Destination not found.');
            $this->redirect('/admin/destinations');
        }

        $this->render('admin/destinations/form', [
            'title' => 'Edit Destination: ' . $destination['name'],
            'destination' => $destination
        ], 'admin');
    }

    public function update(Request $request, Response $response, string $id): void {
        $destModel = new Destination();
        $destination = $destModel->find((int)$id);

        if (!$destination) {
            $this->flash('error', 'Destination not found.');
            $this->redirect('/admin/destinations');
        }

        $data = $request->getAllPost();
        $rules = [
            'name' => 'required|min:2',
            'slug' => "required|unique:destinations,slug,{$id}",
            'country' => 'required',
        ];

        $errors = $this->validate($data, $rules);
        if ($errors) {
            $this->redirect('/admin/destinations/edit/' . $id);
        }

        $destModel->update((int)$id, [
            'name' => trim($data['name']),
            'slug' => strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['slug']))),
            'country' => trim($data['country']),
            'state' => trim($data['state'] ?? ''),
            'city' => trim($data['city'] ?? ''),
            'description' => trim($data['description'] ?? ''),
            'image_url' => trim($data['image_url'] ?? ''),
            'is_featured' => !empty($data['is_featured']) ? 1 : 0,
            'status' => $data['status'] ?? 'active',
        ]);

        AuditLog::record('destination_updated', 'destination', (int)$id);
        $this->flash('success', 'Destination updated successfully.');
        $this->redirect('/admin/destinations');
    }

    public function delete(Request $request, Response $response, string $id): void {
        $destModel = new Destination();
        $destModel->delete((int)$id);

        AuditLog::record('destination_deleted', 'destination', (int)$id);
        $this->flash('success', 'Destination removed.');
        $this->redirect('/admin/destinations');
    }
}
