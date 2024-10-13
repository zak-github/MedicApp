@extends('layouts.base')

@section('content')

<style>
  .Ptable {
    width: auto;
    max-width: 100%;
    border-collapse: collapse;
    box-shadow: 0 0 20px white;
    margin: 20px 0;
    font-size: 18px;
    background-color: #ffffff;
  }
  th, td {
    padding: 12px 15px;
    text-align: left;
    white-space: nowrap;
  }
  th {
    background-color: blue;
    color: blue;

    font-weight: bold;
  }
  tr {
    border-bottom: 1px solid #dddddd;
  }
  tr:nth-of-type(even) {
    background-color: #f3f3f3;
  }
  tr:last-of-type {
    border-bottom: 2px solid blue;
  }
  tr:hover {
    cursor: pointer;
    box-shadow: 0 5px 15px rgba(255, 0, 0, 0.5), 
                0 10px 20px rgba(0, 255, 0, 0.5), 
                0 15px 25px rgba(0, 0, 255, 0.5);
  }

  /*** css div alert */
  .alert {
  padding: 20px;
  background-color: #f44336; /* Red background */
  color: white; /* White text */
  margin-bottom: 15px;
  border-radius: 5px; /* Rounded corners */
  position: relative; /* For the close button */
  width:350px;
}

.alert .closebtn {
  margin-left: 15px;
  color: white;
  font-weight: bold;
  float: right;
  font-size: 22px;
  line-height: 20px;
  cursor: pointer;
  transition: 0.3s;
}

.alert .closebtn:hover {
  color: black;
}

</style>

<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
      {{ __('Liste Des Patients') }}
    </h2>
    <div style="text-align:right">
      <a href="{{url('patients/create')}}">
        <x-primary-button>{{ __('Ajouter Un Patient') }}</x-primary-button>
      </a>
    </div>

    <div class="search">
                    <label>
                        <input id="search" type="text" placeholder="Recherche Patient">
                        <ion-icon name="search-outline"></ion-icon>
                    </label>
      </div>
  </x-slot>

  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
      <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
        

     
    @if (session('success'))
    <div class="alert " style="background-color:rgba(233, 210, 86, 0.8)">
        {{ session('success') }}
    </div>
@endif
@if (session('add'))
    <div class="alert " style="background-color:rgba(118, 224, 63, 0.8)">
        {{ session('add') }}
    </div>
@endif
@if (session('del'))
    <div class="alert " style="background-color:rgba(226, 49, 29, 0.8)">
        {{ session('del') }}
    </div>
@endif

        @if($pat)
        <table class="Ptable">
          <thead>
            <tr>
              <td>ID</td>
              <td>Nom & Prénom</td>
              <td>CIN</td>
              <td>Numéros Téléphone</td>
              <td>Nombre Session</td>
              <td>Date Visite</td>
              <td>Action</td>
            </tr>
          </thead>
          <tbody>
            @foreach($pat as $pats)
            <tr onclick="showLastCellContent(this)">
              <td>{{$pats->id}}</td>
              <td>{{$pats->name}} </td>
              <td>{{$pats->cin}}</td>
              <td>{{$pats->tel}}</td>
              <td>{{$pats->session}}</td>
              <td>{{$pats->rdv}}</td>
              <td>
                <a class="status pending" href="{{url('patients/'.$pats->id.'/edit')}}" onclick="event.stopPropagation(); ">Edit</a>
                <a class="status return"  onclick="event.stopPropagation(); deletePatient({{$pats->id}})">Delete</a>
              </td>
              <td style="display:none">{{$pats->id}}</td>
            </tr>
            @endforeach
            
          </tbody>
          <!-- Pagination links -->
{{ $pat->links() }}
        </table>
        @else
        <h4>Non information existe !!</h4>
        @endif
      </div>
    </div>
  </div>
</x-app-layout>

<script>
  function showLastCellContent(row) {
    const lastTd = row.querySelector('td:last-child').textContent;
   // alert(`id patient: ${lastTd}`);
   window.location.href = `/patients/${lastTd}/details`;
  }

 
  function deletePatient(patientId) {
    // Confirm deletion
    if (confirm('Êtes-vous sûr de vouloir supprimer ce patient ? !!')) {
      // Redirect to the delete endpoint (or you could use AJAX here)
      //window.location.href = `/patients/${patientId}/delete`;


fetch('/delet', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ id: patientId }) 
            })
            .then(response => response.json())
            .then(data => {
                console.log(data);
            
            })
            .catch(error => {
                console.error('Error:', error);

                // Handle any errors
            });
         // Reload the current page
location.reload();

    }// end confirm
  }


  $(document).ready(function() {
            setTimeout(function() {
                $('.alert').fadeOut('slow', function() {
                    $(this).remove();
                });
            }, 3000); // 3 seconds before fade out
        });


        $(function() {
            $("#search").autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: "{{ url('/patients/names') }}",
                        data: {
                            term: request.term
                        },
                        success: function(data) {
                            //response(data.name);
                            console.log(data)

                           /**start new */ response(data.map(function(patient) {
                        return {
                            label: "Patient  :" +patient.name + " -  CIN : " + patient.cin, // Display both name and cin
                            value: patient.name, // Set the input field's value to the selected name
                            idpat:patient.id
                        };
                    }));/** end new code */


                        }//end success
                        
                    });
                },
                minLength: 2,
                select: function(event, ui) {
                    //$('#name').val(ui.item.value);
                    var idpatient = ui.item.idpat;
                    window.location.href = `/patients/${idpatient}/details`;

                }
            });
        });
</script>
@endsection
