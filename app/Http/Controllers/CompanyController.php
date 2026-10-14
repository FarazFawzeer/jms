<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Customer;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->canManageSystem(), 403, 'Only an admin of the Overall division can change company details.');

        return view('company.index', ['company' => Company::current()]);
    }

    public function update(Request $request)
    {
        abort_unless($request->user()->canManageSystem(), 403);

        $request->merge([
            'tel1' => Customer::normalizePhone($request->input('tel1')),
            'tel2' => Customer::normalizePhone($request->input('tel2')),
            'email' => mb_strtolower(trim((string) $request->input('email'))) ?: null,
        ]);

        $v = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'add1' => ['nullable', 'string', 'max:50'],
            'add2' => ['nullable', 'string', 'max:250'],
            'add3' => ['nullable', 'string', 'max:250'],
            'tel1' => ['nullable', 'regex:/^94\d{9}$/'],
            'tel2' => ['nullable', 'regex:/^94\d{9}$/'],
            'email' => ['nullable', 'email', 'max:50'],
            'buy_rate' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'sell_rate' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'barcode_printer' => ['nullable', 'string', 'max:150'],
            'ticket_printer' => ['nullable', 'string', 'max:150'],
            'doc_printer' => ['nullable', 'string', 'max:150'],
            'barcode_port' => ['nullable', 'string', 'max:50'],
            'ticket_port' => ['nullable', 'string', 'max:50'],
            'doc_port' => ['nullable', 'string', 'max:50'],
        ], [
            'name.required' => 'Please enter the company name.',
            'tel1.regex' => 'Enter a 10 digit phone number, like 077 123 4567.',
            'tel2.regex' => 'Enter a 10 digit phone number, like 077 123 4567.',
            'email.email' => 'Enter a valid email address.',
        ]);

        $up = fn ($s) => $s === null ? null : mb_strtoupper(trim($s));

        $data = [
            'comp_desc' => $up($v['name']),
            'Add1' => $up($v['add1'] ?? null),
            'Add2' => $up($v['add2'] ?? null),
            'Add3' => $up($v['add3'] ?? null),
            'Tel1' => $v['tel1'] ?? null,
            'Tel2' => $v['tel2'] ?? null,
            'E_MailAddress' => $v['email'] ?? null,
            'Pu_Rate' => (float) ($v['buy_rate'] ?? 0),
            'Sel_Rate' => (float) ($v['sell_rate'] ?? 0),
            'BarcodeP' => $v['barcode_printer'] ?? null,
            'TicketP' => $v['ticket_printer'] ?? null,
            'DocP' => $v['doc_printer'] ?? null,
            'BarcodePort' => $v['barcode_port'] ?? null,
            'TicketPort' => $v['ticket_port'] ?? null,
            'DocPort' => $v['doc_port'] ?? null,
        ];

        $company = Company::current();
        $company->exists
            ? Company::query()->update($data) // single row, keyed by comp_code only
            : Company::create($data + ['comp_code' => Company::COMPANY, 'status' => 'A']);

        return response()->json([
            'success' => true,
            'message' => 'Company details updated.',
        ]);
    }
}
