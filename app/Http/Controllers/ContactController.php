<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Contact;
use App\Services\PhoneService;

class ContactController extends Controller
{
    public function syncContact(Request $request)
    {
        // validation to the request (return 422 if failed)
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'contacts' => 'required|array|min:1|max:1000',
            'contacts.*.name' => 'required|string|max:255',
            'contacts.*.phone' => 'required|string'
        ]);

        // normalize all contacts
        // change the array into collection for mapping
        $contacts = collect($validated['contacts'])->map(function ($contact) {
            return [
                'name' => trim($contact['name']),
                'phone' => $contact['phone'],
                'normalized_phone' => PhoneService::normalize($contact['phone']),
            ];
        });

        // get all normalized phones from contacts for matching and umatching
        $phones = $contacts->pluck('normalized_phone')->unique();

        $matched = [];
        $unmatched = [];

        // find matched users in the query with normalized phones
        $matchedUsers = User::whereIn('normalized_phone', $phones)
            ->get()
            ->keyBy('normalized_phone');

        foreach ($contacts as $contact) {

            $normalizedPhone = $contact['normalized_phone'];

            if ($matchedUsers->has($normalizedPhone)) {

                $matched[] = [
                    'name' => $contact['name'],
                    'phone' => $normalizedPhone,
                    'user_id' => $matchedUsers[$normalizedPhone]->id
                ];

            } else {

                $unmatched[] = [
                    'name' => $contact['name'],
                    'phone' => $normalizedPhone
                ];
            }
        }

        // deduplicate contacts by normalized phone
        $contactsForInsert = $contacts->unique('normalized_phone')->values();

        // only if user_id provided, store contacts
        if (!empty($validated['user_id'])) {

            $userId = $validated['user_id'];
            // pass the outside userId variable into the fuction to use inside
            $contactsForInsert = $contactsForInsert->map(function ($contact) use ($userId) {
                return [
                    'user_id' => $userId,
                    'name' => $contact['name'],
                    'phone' => $contact['phone'],
                    'normalized_phone' => $contact['normalized_phone'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            });

            // bulk insertions (upsert  = INSERT + UPDATE)
            Contact::upsert(
                $contactsForInsert->toArray(), // data to insert
                ['user_id', 'normalized_phone'], // do not isert if these found the same 
                ['name', 'phone', 'updated_at'] // overwrite if duplicate found
            );
        }

        // final response
        return response()->json([
            'total_uploaded' => $contacts->count(),
            'matched' => $matched,
            'unmatched' => $unmatched
        ]);
    }
}