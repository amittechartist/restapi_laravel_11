<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Services\FileUploadService;

class SupportTicketController extends Controller
{
    protected $uploadService;

    public function __construct(FileUploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    /**
     * Create a new support ticket.
     *
     * Expected payload (multipart/form-data):
     * - subject (string)
     * - message (string)
     * - file (optional file)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createTicket(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string',
            'message' => 'required|string',
            'file'    => 'nullable|file|max:10240', // up to 10MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $data = $validator->validated();
        $filePath = null;

        if ($request->hasFile('file')) {
            $result = $this->uploadService->upload($request->file('file'), 'support_tickets');
            $filePath = $result['path'];
        }

        $ticket = Ticket::create([
            'user_id' => $request->user()->id,
            'subject' => $data['subject'],
            'message' => $data['message'],
            'file'    => $filePath,
            'status'  => 'open'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Support ticket created successfully.',
            'data'    => $ticket
        ]);
    }

    /**
     * List all support tickets.
     * Admin-only endpoint.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function listTickets()
    {
        $tickets = Ticket::orderBy('created_at', 'desc')->get();
        return response()->json([
            'success' => true,
            'data'    => $tickets
        ]);
    }

    /**
     * Show a specific ticket with its replies.
     * Accessible to ticket owner or admin.
     *
     * @param int $ticketId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function showTicket($ticketId, Request $request)
    {
        $ticket = Ticket::with('replies.user')->find($ticketId);
        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found.'
            ], 404);
        }

        // Only owner or admin can view
        if ($request->user()->id !== $ticket->user_id && !$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data'    => $ticket
        ]);
    }

    /**
     * Add a reply to a support ticket.
     * Both user and admin can reply if the ticket is open.
     *
     * Expected payload (multipart/form-data):
     * - message (string, optional if file is provided)
     * - file (optional file)
     *
     * @param Request $request
     * @param int $ticketId
     * @return \Illuminate\Http\JsonResponse
     */
    public function replyTicket(Request $request, $ticketId)
    {
        $ticket = Ticket::find($ticketId);
        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found.'
            ], 404);
        }
        if ($ticket->status === 'closed') {
            return response()->json([
                'success' => false,
                'message' => 'Ticket is closed; no further replies allowed.'
            ], 403);
        }

        // Only the ticket owner or an admin can reply.
        if ($request->user()->id !== $ticket->user_id && !$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'message' => 'nullable|string',
            'file'    => 'nullable|file|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        // At least one of message or file is required.
        if (!$request->has('message') && !$request->hasFile('file')) {
            return response()->json([
                'success' => false,
                'message' => 'Either message or file must be provided.'
            ], 422);
        }

        $filePath = null;
        if ($request->hasFile('file')) {
            $result = $this->uploadService->upload($request->file('file'), 'support_ticket_replies');
            $filePath = $result['path'];
        }

        $reply = TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $request->user()->id,
            'message'   => $request->input('message', ''),
            'file'      => $filePath
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reply added successfully.',
            'data'    => $reply
        ]);
    }

    /**
     * Close a support ticket.
     * Only admin can close a ticket.
     *
     * @param int $ticketId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function closeTicket($ticketId, Request $request)
    {
        // Admin-only; you may also enforce this via middleware.
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.'
            ], 403);
        }

        $ticket = Ticket::find($ticketId);
        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found.'
            ], 404);
        }

        $ticket->status = 'closed';
        $ticket->save();

        return response()->json([
            'success' => true,
            'message' => 'Ticket closed successfully.',
            'data'    => $ticket
        ]);
    }
}
