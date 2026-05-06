@extends('layouts.main')

@section("title", "Login")
@section('content')

    <link rel="stylesheet" href="../../css/register.styles.css">

    <div class="register-container shadow-sm">
        <h2 class="mb-1">Glicodata</h2>
        <p class="mb-4">Crie uma conta</p>

        <form action="{{ route('web') }}" method="POST" , class="form">
            @csrf

            <div class="text-start mb-3">
                <label class="form-label">Nome Completo do Paciente:</label>
                <input type="text" name="patient_name" class="form-control" placeholder="Ex: Vitor de Oliveira Monteiro">
            </div>

            <div class="text-start mb-3">
                <label class="form-label">UBS Correspondente:</label>
                <input type="text" name="ubs" class="form-control" placeholder="Ex: Uvaranas / Giana">
            </div>

            <div class="text-start mb-3">
                <label class="form-label">Cartão SUS:</label>
                <input type="text" name="sus_number" class="form-control" placeholder="Ex: 38239109256">
            </div>

            <div class="text-start mb-4">
                <label class="form-label">Nome Completo do Médico:</label>
                <input type="text" name="doctor_name" class="form-control" placeholder="Ex: João da Silva Sauro">
            </div>

            <button type="submit" class="btn btn-dark w-100 mb-3">
                Registre-se
            </button>
        </form>

        <div class="divider"><span>ou continue com</span></div>

        <button class="google-btn">
            <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" width="22">
            Google
        </button>

        <p class="mt-4" style="font-size: 0.85rem; color:#777;">
            By clicking continue, you agree to our
            <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
        </p>
    </div>
@endsection