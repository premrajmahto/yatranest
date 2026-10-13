<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\AuditLog;
use App\Models\TourDeparture;
use App\Models\TourPackage;

class DepartureManagementController extends Controller {
    public function index(Request $request, Response $response): void {
        $depModel = new TourDeparture();
        $sql = "SELECT d.*, p.title AS package_title, dest.name AS destination_name
                FROM tour_departures d
                JOIN tour_packages p ON d.package_id = p.id
                JOIN destinations dest ON p.destination_id = dest.id
                ORDER BY d.departure_date ASC";
        $departures = $depModel->fetchAll($sql);

        $this->render('admin/departures/index', [
            'title' => 'Departures & Seat Inventory',
            'departures' => $departures
        ], 'admin');
    }

    public function create(Request $request, Response $response): void {
        $pkgModel = new TourPackage();
        $this->render('admin/departures/form', [
            'title' => 'Schedule Departure',
            'departure' => [],
            'packages' => $pkgModel->all('title ASC')
        ], 'admin');
    }

    public function store(Request $request, Response $response): void {
        $data = $request->getAllPost();
        $rules = [
            'package_id' => 'required|integer',
            'departure_date' => 'required|date',
            'return_date' => 'required|date',
            'total_seats' => 'required|integer|min:1',
            'available_seats' => 'required|integer|min:0',
        ];

        $errors = $this->validate($data, $rules);
        if ($errors) {
            $this->redirect('/admin/departures/create');
        }

        $depModel = new TourDeparture();
        $id = $depModel->create([
            'package_id' => (int)$data['package_id'],
            'departure_date' => $data['departure_date'],
            'return_date' => $data['return_date'],
            'total_seats' => (int)$data['total_seats'],
            'available_seats' => (int)$data['available_seats'],
            'adult_price' => !empty($data['adult_price']) ? (float)$data['adult_price'] : null,
            'child_price' => !empty($data['child_price']) ? (float)$data['child_price'] : null,
            'status' => $data['status'] ?? 'open',
        ]);

        AuditLog::record('departure_created', 'tour_departure', $id);
        $this->flash('success', 'Departure batch created successfully.');
        $this->redirect('/admin/departures');
    }

    public function edit(Request $request, Response $response, string $id): void {
        $depModel = new TourDeparture();
        $departure = $depModel->find((int)$id);

        if (!$departure) {
            $this->flash('error', 'Departure schedule not found.');
            $this->redirect('/admin/departures');
        }

        $pkgModel = new TourPackage();
        $this->render('admin/departures/form', [
            'title' => 'Edit Departure Schedule',
            'departure' => $departure,
            'packages' => $pkgModel->all('title ASC')
        ], 'admin');
    }

    public function update(Request $request, Response $response, string $id): void {
        $depModel = new TourDeparture();
        $departure = $depModel->find((int)$id);

        if (!$departure) {
            $this->flash('error', 'Departure not found.');
            $this->redirect('/admin/departures');
        }

        $data = $request->getAllPost();
        $rules = [
            'package_id' => 'required|integer',
            'departure_date' => 'required|date',
            'return_date' => 'required|date',
            'total_seats' => 'required|integer|min:1',
            'available_seats' => 'required|integer|min:0',
        ];

        $errors = $this->validate($data, $rules);
        if ($errors) {
            $this->redirect('/admin/departures/edit/' . $id);
        }

        $depModel->update((int)$id, [
            'package_id' => (int)$data['package_id'],
            'departure_date' => $data['departure_date'],
            'return_date' => $data['return_date'],
            'total_seats' => (int)$data['total_seats'],
            'available_seats' => (int)$data['available_seats'],
            'adult_price' => !empty($data['adult_price']) ? (float)$data['adult_price'] : null,
            'child_price' => !empty($data['child_price']) ? (float)$data['child_price'] : null,
            'status' => $data['status'] ?? 'open',
        ]);

        AuditLog::record('departure_updated', 'tour_departure', (int)$id);
        $this->flash('success', 'Departure schedule updated.');
        $this->redirect('/admin/departures');
    }

    public function delete(Request $request, Response $response, string $id): void {
        $depModel = new TourDeparture();
        $depModel->delete((int)$id);

        AuditLog::record('departure_deleted', 'tour_departure', (int)$id);
        $this->flash('success', 'Departure schedule deleted.');
        $this->redirect('/admin/departures');
    }
}
