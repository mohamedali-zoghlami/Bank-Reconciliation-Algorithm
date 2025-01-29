@extends('user.master')

@section('content')
<div class="col-lg-12 grid-margin stretch-card">
    <div class="card">
        <div class="card-body">
            <div class=" d-flex justify-content-between"><h4 class="card-title">Faire un rapprochement</h4>
                <div class="">
                <button type="button" id="rapprochement" class="btn btn-outline-primary btn-icon-text">
                    <i class="ti-file btn-icon-prepend"></i>
                    Lancer Rapprochement
                  </button>
            </div></div>

            <form method="POST" action="">
                @csrf
                <div class="form-group">
                    <label for="contact">Entreprise</label>
                    <select class="form-control" id="entreprise" name="entreprise" required >
                        <option value="">Sélectionnez une entreprise</option>
                        @foreach ($entreprises as $entreprise )
                            <option value="{{$entreprise->id}}">{{$entreprise->nom}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="contact">Mois du rapprochement</label>
                    <input type="month" class="form-control" id="mois" name="mois">
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Methode :</label>
                    <div class="col-sm-4">
                      <div class="form-check">
                        <label class="form-check-label">
                          <input type="radio" class="form-check-input" name="methode" value="excel" checked="">
                          Excel
                        <i class="input-helper"></i></label>
                      </div>
                    </div>
                    <div class="col-sm-5">
                      <div class="form-check">
                        <label class="form-check-label">
                          <input type="radio" class="form-check-input" name="methode" value="web">
                          Formulaire Web
                        <i class="input-helper"></i></label>
                      </div>
                    </div>
                </div>
            </form>
                <div id="excel">
                    <div class="form-group">
                        <label for="formFile">Grand Livre Bancaire : <a href={{ route('template.download', "journal") }} id="downloadJournal"> Telecharger template</a> </label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="grandlivre" name="grandlivre" accept=".xlsx, .xls">
                            <label class="custom-file-label" for="customFile">Charger le Grand livre bancaire</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="formFile">Releve Bancaire: <a href={{ route('template.download', "releve") }} id="downloadReleve"> Telecharger template</a> </label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input"  id="releve" name="releve" accept=".xlsx, .xls">
                            <label class="custom-file-label" for="customFile">Charger le releve bancaire</label>
                        </div>
                    </div>
                </div>

                <div id="web" style="display:none">
                    <ul class="nav nav-pills nav-fill mb-3">
                        <li class="nav-item">
                          <a class="nav-link active" id="journalbutton" href="#">Grand livre bancaire </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="relevebutton" href="#">Relevée bancaire</a>
                        </li>
                    </ul>
                    <div id="journaldiv" >
                    @include("user.formjournal")
                    <div class="table-responsive">
                        <table class="table table-hover">
                          <thead>
                            <tr>
                              <th>Nom du client</th>
                              <th>Numéro Facture</th>
                              <th>Libellé</th>
                              <th>Date</th>
                              <th>Debit </th>
                              <th>Credit</th>
                              <th>Solde</th>
                            </tr>
                          </thead>
                          <tbody id="jouranlbody">

                          </tbody>
                        </table>
                      </div>
                    </div>
                    <div id="relevediv" style="display:none">
                        @include("user.formreleve")
                        <div class="table-responsive">
                            <table class="table table-hover">
                              <thead>
                                <tr>
                                    <th>Libelle</th>
                                  <th>Date</th>
                                  <th>Debit </th>
                                  <th>Credit</th>
                                  <th>Solde </th>
                                </tr>
                              </thead>
                              <tbody id="relevebody">

                              </tbody>
                            </table>
                        </div>
                    </div>
                </div>

        </div>
    </div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="{{asset('js/same.js')}}"></script>
<script src="{{asset('js/excel.js')}}"></script>
<script src="{{asset('js/errors.js')}}"></script>

<script>

    let journal=[]
    let releve=[]
    if (localStorage.getItem('journal')) {
         journal = JSON.parse(localStorage.getItem('journal'));
    }
    if (localStorage.getItem('releve')) {
        releve = JSON.parse(localStorage.getItem('releve'));
    }
    journal.forEach(element => {
        const html="<tr><td>"+element.lib+"</td><td>"+element.Numero+"</td><td>"+element.lib+"</td><td>"+element.date+"</td><td>"+element.debit+"</td><td>"+element.credit+"</td><td>"+element.solde+"</td></tr>"
    $("#jouranlbody").append(html);
    });
    releve.forEach(element => {
        const html="<tr><td>"+element.Operation+"</td><td>"+element.date+"</td><td>"+element.debit+"</td><td>"+element.credit+"</td><td>"+element.solde+"</td></tr>"
    $("#jouranlbody").append(html);
    });
    $(".custom-file-input").on("change", function() {
      const fileName = $(this).val().split("\\").pop();
      $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
    });
    $('input[type="radio"]').on('change', function(e) {
        const methode=$('input[name="methode"]:checked').val();
        $("#"+methode).show()
        if(methode=="web")
            $("#excel").hide()
        else
            $("#web").hide()
    });
    $("#journalbutton").on("click",function(){
        $("#journaldiv").show();
        $("#relevediv").hide();
        $("#journalbutton").addClass("active");
        $("#relevebutton").removeClass("active");
    })
    $("#relevebutton").on("click",function(){
        $("#journaldiv").hide();
        $("#relevediv").show();
        $("#journalbutton").removeClass("active");
        $("#relevebutton").addClass("active");
    })
    function addJournal(){
        const client=$("#client").val();
        const num=$("#numero").val();
        const date=$("#date").val();
        let debit=$("#debit").val()
        let credit=$("#credit").val()
        const lib=$("#libelleJournal").val();
        if(!debit)
            debit=0
        else
            debit=parseFloat(debit)
        if(!credit)
            credit=0
        else
            credit=parseFloat(credit)
        const solde=debit-credit;
        $("#client").val("");
        $("#numero").val("");
        $("#date").val("");
        $("#libelleJournal").val("")
        $("#debit").val("")
        $("#credit").val("")
        const facture=
        {"Num_Compte":client,
        "date":date,
        "Numero":num,
        "lib":lib,
        "debit":debit,
        "credit":credit,
        "solde": solde,
        "lettrage":undefined
        };

    journal.push(facture);
    localStorage.setItem("journal",JSON.stringify(journal));
    const html="<tr><td>"+client+"</td><td>"+num+"</td><td>"+lib+"</td><td>"+date+"</td><td>"+debit+"</td><td>"+credit+"</td><td>"+solde+"</td></tr>"
    $("#jouranlbody").append(html);
    }
    errorDownload("downloadJournal");
    errorDownload("downloadReleve");
    function addReleve(){
        const lib=$("#libelle").val();
        const date=$("#date2").val();
        let debit=$("#debit2").val();
        let credit=$("#credit2").val();
        if(!debit)
            debit=0
        else
            debit=parseFloat(debit)
        if(!credit)
            credit=0
        else
            credit=parseFloat(credit)
        const solde=debit-credit;
        $("#libelle").val("");
        $("#date2").val("");
        $("#debit2").val("");
        $("#credit2").val("");
        const rel={
            "date":date,
            "Operation":lib,
            "debit":debit,
            "credit":credit,
            "solde": solde,
            "lettrage":undefined
        }
        releve.push(rel);
        localStorage.setItem("releve",JSON.stringify(releve));
        const html="<tr><td>"+lib+"</td><td>"+date+"</td><td>"+debit+"</td><td>"+credit+"</td><td>"+solde+"</td></tr>"
        $("#relevebody").append(html);
    }
    $('#deleteJournal').click(function() {
        Swal.fire({
            title: 'Êtes-vous sûr?',
            text: "Vous ne pourrez pas revenir en arrière!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oui, supprimez-le!'
        }).then((result) => {
            if (result.isConfirmed) {
                localStorage.removeItem('journal');
                $('#jouranlbody').empty();
                journal=[]
            }
        });
    });
    $('#deleteReleve').click(function() {
        Swal.fire({
            title: 'Êtes-vous sûr?',
            text: "Vous ne pourrez pas revenir en arrière!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oui, supprimez-le!'
        }).then((result) => {
            if (result.isConfirmed) {
                localStorage.removeItem('releve');
                $('#relevebody').empty();
                releve=[];
            }
        });
    });
    $("#rapprochement").on("click",async function(){
        $('#rapprochement').prop('disabled', true);
        const entreprise=$("#entreprise").val();
        const month=$("#mois").val();
        const methode=$('input[name="methode"]:checked').val();
        if(entreprise=="")
            {
                Swal.fire({
                icon: "error",
                title: "Erreur",
                text: "Veuillez sélectionnez une entrepirse !",
                });
                $('#rapprochement').prop('disabled', false);
                return;
            }
        if(month=="")
            {
                Swal.fire({
                icon: "error",
                title: "Oups...",
                text: "Veuillez sélectionnez un mois !",
                });
                $('#rapprochement').prop('disabled', false);
                return;
            }
        if(methode==="excel")
        {
        const fileInput1 = document.getElementById('releve');
        const fileInput2 = document.getElementById('grandlivre');
        const file1 = fileInput1.files[0];
        const file2 = fileInput2.files[0];
        if(!file1 || !file2)
        {
            Swal.fire({
                icon: "error",
                title: "Oups...",
                text: "Veuillez charger le grand livre bancaire et le releve bancaire !",
                });
                $('#rapprochement').prop('disabled', false);
                return;
        }
        type='application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        if(file1.type!==type || file2.type!==type)
        {
            Swal.fire({
                icon: "error",
                title: "Oups...",
                text: "Veuillez charger des fichiers excels !",
                });
                $('#rapprochement').prop('disabled', false);
                return;
        }
        lastSubmission(month,entreprise,function(response){
            if (response.error)
               { Swal.fire({
                icon: "error",
                title: "Oups...",
                text:response.error,
                });
                $('#rapprochement').prop('disabled', false);
                return;
            }
             Swal.fire({
                title: 'Chargement...',
                text: "Le rapprochement est entrain d'exectuer veuillez attendre un moment !",
                allowOutsideClick: false,
                didOpen: () => {
                Swal.showLoading()
                },
                timer: 60000,
                timerProgressBar: true,
            })
        excel(file2,file1,response).then(result=>{
            if (typeof result === 'string') {
                Swal.fire({
                icon: "error",
                title: "Oups...",
                text:result,
                });
                $('#rapprochement').prop('disabled', false);
                return;
            }
            else
            {
            sendRapprochement(result,month,entreprise)
            }
        });})
       }
       else
       {
        if(journal.length==0){
             Swal.fire({
                icon: "error",
                title: "Oups...",
                text:"Grand livre bancaire est vide",
                });
                $('#rapprochement').prop('disabled', false);
                return;
            }
            if(releve.length==0){
             Swal.fire({
                icon: "error",
                title: "Oups...",
                text:"Releve bancaire est vide",
                });
                $('#rapprochement').prop('disabled', false);
                return;
            }
        lastSubmission(month,entreprise,function(response){
        let result=same(journal,releve,response);
        sendRapprochement(result,month,entreprise);
    })
    }

})

function lastSubmission(date,entreprise,callback)
{
    $.ajax({
        url: '/lastSubmission',
        type: 'GET',
        data: {
            date: date,
            entreprise: entreprise,
        },
        success: function(response) {
            callback(response);
        },
        error : function(){
            callback([]);
        }
    })
}

function sendRapprochement(array,date,entreprise){

    $.ajax({
    url: '/create-rapprochement',
    type: 'POST',
    data: {
        _token: '{{ csrf_token() }}',
        date: date,
        entreprise: entreprise,
        journal: JSON.stringify(array[0]),
        releve:JSON.stringify(array[1])
    },
    success: function(response) {
        if (response.success) {
            localStorage.removeItem('journal');
            localStorage.removeItem('releve');
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Rapprochement fait avec succès',
            }).then(()=>{
                window.location.href = '/rapprochement/historique';
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erreur inattendu !',
            });
            $('#rapprochement').prop('disabled', false);
        }
    },
    error: function(xhr, status, error) {
        console.error(xhr.responseText);
        Swal.fire({
            icon: 'error',
            title: 'Erreur inattendu !',
            text: xhr.responseText
        });
        $('#rapprochement').prop('disabled', false);

    }
});
}



</script>
@endsection
