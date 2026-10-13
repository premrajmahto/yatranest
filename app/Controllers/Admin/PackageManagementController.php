<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\AuditLog;
use App\Models\Destination;
use App\Models\TourCategory;
use App\Models\TourPackage;

class PackageManagementController extends Controller {
    public function index(Request $request, Response $response): void {
        $pkgModel = new TourPackage();
        $sql = "SELECT p.*, d.name AS destination_name, c.name AS category_name
                FROM tour_packages p
                JOIN destinations d ON p.destination_id = d.id
                JOIN tour_categories c ON p.category_id = c.id
                ORDER BY p.id DESC";
        $packages = $pkgModel->fetchAll($sql);

        $this->render('admin/packages/index', [
            'title' => 'Tour Packages Management',
            'packages' => $packages
        ], 'admin');
    }

    public function create(Request $request, Response $response): void {
        $destModel = new Destination();
        $catModel = new TourCategory();

        $this->render('admin/packages/form', [
            'title' => 'Create New Package',
            'package' => [],
            'destinations' => $destModel->all('name ASC'),
            'categories' => $catModel->all('name ASC')
        ], 'admin');
    }

    public function store(Request $request, Response $response): void {
        $data = $request->getAllPost();
        $rules = [
            'title' => 'required|min:3',
            'slug' => 'required|unique:tour_packages,slug',
            'destination_id' => 'required|integer',
            'category_id' => 'required|integer',
            'price_adult' => 'required|numeric',
            'duration_days' => 'required|integer|min:1',
            'duration_nights' => 'required|integer|min:0',
            'description' => 'required|min:10',
        ];

        $errors = $this->validate($data, $rules);
        if ($errors) {
            $this->redirect('/admin/packages/create');
        }

        $pkgModel = new TourPackage();
        $packageId = $pkgModel->create([
            'title' => trim($data['title']),
            'slug' => strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['slug']))),
            'destination_id' => (int)$data['destination_id'],
            'category_id' => (int)$data['category_id'],
            'summary' => trim($data['summary'] ?? ''),
            'description' => trim($data['description']),
            'price_adult' => (float)$data['price_adult'],
            'price_child' => (float)($data['price_child'] ?? 0),
            'price_infant' => (float)($data['price_infant'] ?? 0),
            'duration_days' => (int)$data['duration_days'],
            'duration_nights' => (int)$data['duration_nights'],
            'group_size_max' => (int)($data['group_size_max'] ?? 20),
            'inclusions' => trim($data['inclusions'] ?? ''),
            'exclusions' => trim($data['exclusions'] ?? ''),
            'hotel_details' => trim($data['hotel_details'] ?? ''),
            'cancellation_policy_text' => trim($data['cancellation_policy_text'] ?? ''),
            'featured_image' => trim($data['featured_image'] ?? ''),
            'is_featured' => !empty($data['is_featured']) ? 1 : 0,
            'is_domestic' => !empty($data['is_domestic']) ? 1 : 0,
            'status' => $data['status'] ?? 'published',
            'seo_title' => trim($data['seo_title'] ?? $data['title']),
            'seo_meta_description' => trim($data['seo_meta_description'] ?? ''),
        ]);

        AuditLog::record('package_created', 'tour_package', $packageId);
        $this->flash('success', 'Tour Package created successfully.');
        $this->redirect('/admin/packages');
    }

    public function edit(Request $request, Response $response, string $id): void {
        $pkgModel = new TourPackage();
        $package = $pkgModel->find((int)$id);

        if (!$package) {
            $this->flash('error', 'Package not found.');
            $this->redirect('/admin/packages');
        }

        $destModel = new Destination();
        $catModel = new TourCategory();

        $this->render('admin/packages/form', [
            'title' => 'Edit Package: ' . $package['title'],
            'package' => $package,
            'destinations' => $destModel->all('name ASC'),
            'categories' => $catModel->all('name ASC')
        ], 'admin');
    }

    public function update(Request $request, Response $response, string $id): void {
        $pkgModel = new TourPackage();
        $package = $pkgModel->find((int)$id);

        if (!$package) {
            $this->flash('error', 'Package not found.');
            $this->redirect('/admin/packages');
        }

        $data = $request->getAllPost();
        $rules = [
            'title' => 'required|min:3',
            'slug' => "required|unique:tour_packages,slug,{$id}",
            'destination_id' => 'required|integer',
            'category_id' => 'required|integer',
            'price_adult' => 'required|numeric',
            'duration_days' => 'required|integer|min:1',
            'duration_nights' => 'required|integer|min:0',
            'description' => 'required|min:10',
        ];

        $errors = $this->validate($data, $rules);
        if ($errors) {
            $this->redirect('/admin/packages/edit/' . $id);
        }

        $pkgModel->update((int)$id, [
            'title' => trim($data['title']),
            'slug' => strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['slug']))),
            'destination_id' => (int)$data['destination_id'],
            'category_id' => (int)$data['category_id'],
            'summary' => trim($data['summary'] ?? ''),
            'description' => trim($data['description']),
            'price_adult' => (float)$data['price_adult'],
            'price_child' => (float)($data['price_child'] ?? 0),
            'price_infant' => (float)($data['price_infant'] ?? 0),
            'duration_days' => (int)$data['duration_days'],
            'duration_nights' => (int)$data['duration_nights'],
            'group_size_max' => (int)($data['group_size_max'] ?? 20),
            'inclusions' => trim($data['inclusions'] ?? ''),
            'exclusions' => trim($data['exclusions'] ?? ''),
            'hotel_details' => trim($data['hotel_details'] ?? ''),
            'cancellation_policy_text' => trim($data['cancellation_policy_text'] ?? ''),
            'featured_image' => trim($data['featured_image'] ?? ''),
            'is_featured' => !empty($data['is_featured']) ? 1 : 0,
            'is_domestic' => !empty($data['is_domestic']) ? 1 : 0,
            'status' => $data['status'] ?? 'published',
            'seo_title' => trim($data['seo_title'] ?? $data['title']),
            'seo_meta_description' => trim($data['seo_meta_description'] ?? ''),
        ]);

        AuditLog::record('package_updated', 'tour_package', (int)$id);
        $this->flash('success', 'Tour Package updated successfully.');
        $this->redirect('/admin/packages');
    }

    public function delete(Request $request, Response $response, string $id): void {
        $pkgModel = new TourPackage();
        $pkgModel->delete((int)$id);

        AuditLog::record('package_deleted', 'tour_package', (int)$id);
        $this->flash('success', 'Package removed.');
        $this->redirect('/admin/packages');
    }
}
