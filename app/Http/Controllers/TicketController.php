<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio;
use Illuminate\Support\Facades\Mail;
use App\Models\Tickets;
use App\Http\Resources\TicketResource;

class TicketController extends Controller
{
    public function index(){
       
        $tickets = Tickets::active();
        return TicketResource::collection($tickets);
    }

    public function store(Request $request){

        
        $request->validate([
            'email' => 'required|email',
            'name' => 'required',
            'model' => 'required',
            'license' => 'required',
            'phone' => 'required',
            'location' => 'required|exists:locations,id'
        ], [
            'location.exists' => 'Location does not exists'
        ]);
        
        $ticket = Tickets::create($request->all());

        if($ticket){
            $to_name = $request->name;
            $to_email = $request->email;
            $subject = 'Ticket Created Succesfully';
            
            try{
                Mail::send('emails.ticket', ['ticket' => $ticket], function($message) use ($to_name, $to_email, $subject) {
                    $message->to($to_email, $to_name)->subject($subject);
                    $message->from(env('MAIL_FROM_ADDRESS'),env('MAIL_FROM_NAME'));
                });
            }catch(\Exception $e){
                $ticket->delete();
                return response('Email could not be sent',500);
            }finally{
                return new TicketResource($ticket);
            }

        
        }
        return response('Ticket could not be created', 500);
    }

    public function delete(){
        return 'delete';
    }
}
