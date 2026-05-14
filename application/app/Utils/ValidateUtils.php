<?php

namespace App\Utils;

use App\Enums\RiskClassification;
use App\Enums\UserRole;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

trait ValidateUtils
{
    /**
     * @throws ValidationException
     */
    private function validateId(string $id): void
    {
        $id = trim($id);

        if ($id === '' || ! Str::isUuid($id)) {
            throw ValidationException::withMessages([
                'id' => ['O id informado deve ser um UUID valido.'],
            ]);
        }
    }

    /**
     * @throws ValidationException
     */
    private function validateEmail(string $email): void
    {
        $validator = Validator::make(
            ['email' => trim($email)],
            ['email' => ['required', 'email:rfc', 'max:255']]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * @param array<string, mixed> $data
     * @throws ValidationException
     */
    private function validateCreateDistrictData(array $data): void
    {
        $this->validateData($data, [
            'name' => ['required', 'string', 'max:255'],
        ]);
    }

    /**
     * @param array<string, mixed> $data
     * @throws ValidationException
     */
    private function validateUpdateDistrictData(array $data): void
    {
        $this->validateData($data, [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
        ]);
    }

    /**
     * @param array<string, mixed> $data
     * @throws ValidationException
     */
    private function validateCreateUbsData(array $data): void
    {
        $this->validateData($data, [
            'district_id' => ['required', 'uuid', Rule::exists('districts', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'bairro_ref' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique('ubs', 'email')],
            'is_active' => ['required', 'boolean'],
        ]);
    }

    /**
     * @param array<string, mixed> $data
     * @throws ValidationException
     */
    private function validateUpdateUbsData(array $data, string $id): void
    {
        $this->validateData($data, [
            'district_id' => ['sometimes', 'required', 'uuid', Rule::exists('districts', 'id')],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'bairro_ref' => ['sometimes', 'required', 'string', 'max:255'],
            'address' => ['sometimes', 'required', 'string', 'max:255'],
            'phone' => ['sometimes', 'required', 'string', 'max:30'],
            'email' => ['sometimes', 'required', 'email:rfc', 'max:255', Rule::unique('ubs', 'email')->ignore($id)],
            'is_active' => ['sometimes', 'required', 'boolean'],
        ]);
    }

    /**
     * @param array<string, mixed> $data
     * @throws ValidationException
     */
    private function validateCreateUserData(array $data): void
    {
        $this->validateData($data, [
            'ubs_id' => ['required', 'uuid', Rule::exists('ubs', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'age' => ['required', 'integer', 'min:0', 'max:130'],
            'sex' => ['required', 'boolean'],
            'cpf' => ['required', 'string', 'max:20', Rule::unique('users', 'cpf')],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'role' => ['required', Rule::in($this->userRoleValues())],
        ]);
    }

    /**
     * @param array<string, mixed> $data
     * @throws ValidationException
     */
    private function validateUpdateUserData(array $data, string $id): void
    {
        $this->validateData($data, [
            'ubs_id' => ['sometimes', 'required', 'uuid', Rule::exists('ubs', 'id')],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'age' => ['sometimes', 'required', 'integer', 'min:0', 'max:130'],
            'sex' => ['sometimes', 'required', 'boolean'],
            'cpf' => ['sometimes', 'required', 'string', 'max:20', Rule::unique('users', 'cpf')->ignore($id)],
            'address' => ['sometimes', 'required', 'string', 'max:255'],
            'phone' => ['sometimes', 'required', 'string', 'max:30'],
            'email' => ['sometimes', 'required', 'email:rfc', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'password' => ['sometimes', 'required', 'string', 'min:8', 'max:255'],
            'role' => ['sometimes', 'required', Rule::in($this->userRoleValues())],
        ]);
    }

    /**
     * @param array<string, mixed> $data
     * @throws ValidationException
     */
    private function validateCreatePatientData(array $data): void
    {
        $this->validateData($data, [
            'ubs_id' => ['required', 'uuid', Rule::exists('ubs', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'age' => ['required', 'integer', 'min:0', 'max:130'],
            'sex' => ['required', 'boolean'],
            'cpf' => ['required', 'string', 'max:20', Rule::unique('patients', 'cpf')],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'birth' => ['required', 'date'],
        ]);
    }

    /**
     * @param array<string, mixed> $data
     * @throws ValidationException
     */
    private function validateUpdatePatientData(array $data, string $id): void
    {
        $this->validateData($data, [
            'ubs_id' => ['sometimes', 'required', 'uuid', Rule::exists('ubs', 'id')],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'age' => ['sometimes', 'required', 'integer', 'min:0', 'max:130'],
            'sex' => ['sometimes', 'required', 'boolean'],
            'cpf' => ['sometimes', 'required', 'string', 'max:20', Rule::unique('patients', 'cpf')->ignore($id)],
            'address' => ['sometimes', 'required', 'string', 'max:255'],
            'phone' => ['sometimes', 'required', 'string', 'max:30'],
            'birth' => ['sometimes', 'required', 'date'],
        ]);
    }

    /**
     * @param array<string, mixed> $data
     * @throws ValidationException
     */
    private function validateCreateAssessmentData(array $data): void
    {
        $this->validateData($data, [
            'patient_id' => ['required', 'uuid', Rule::exists('patients', 'id')],
            'user_id' => ['required', 'uuid', Rule::exists('users', 'id')],
            'ubs_id' => ['required', 'uuid', Rule::exists('ubs', 'id')],
            'symptoms' => ['required', 'string'],
            'answers' => ['required', 'array'],
        ]);
    }

    /**
     * @param array<string, mixed> $data
     * @throws ValidationException
     */
    private function validateUpdateAssessmentData(array $data): void
    {
        $this->validateData($data, [
            'patient_id' => ['sometimes', 'required', 'uuid', Rule::exists('patients', 'id')],
            'user_id' => ['sometimes', 'required', 'uuid', Rule::exists('users', 'id')],
            'ubs_id' => ['sometimes', 'required', 'uuid', Rule::exists('ubs', 'id')],
            'symptoms' => ['sometimes', 'required', 'string'],
            'answers' => ['sometimes', 'required', 'array'],
        ]);
    }

    /**
     * @param array<string, mixed> $data
     * @throws ValidationException
     */
    private function validateCreateRiskData(array $data): void
    {
        $this->validateData($data, [
            'assessment_id' => ['required', 'uuid', Rule::exists('assessments', 'id'), Rule::unique('risks', 'assessment_id')],
            'percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'classification' => ['required', Rule::in($this->riskClassificationValues())],
            'score' => ['required', 'integer', 'min:0'],
        ]);
    }

    /**
     * @param array<string, mixed> $data
     * @throws ValidationException
     */
    private function validateUpdateRiskData(array $data, string $id): void
    {
        $this->validateData($data, [
            'assessment_id' => ['sometimes', 'required', 'uuid', Rule::exists('assessments', 'id'), Rule::unique('risks', 'assessment_id')->ignore($id)],
            'percentage' => ['sometimes', 'required', 'numeric', 'min:0', 'max:100'],
            'classification' => ['sometimes', 'required', Rule::in($this->riskClassificationValues())],
            'score' => ['sometimes', 'required', 'integer', 'min:0'],
        ]);
    }

    /**
     * @param array<string, mixed> $data
     * @throws ValidationException
     */
    private function validateCreateReportData(array $data): void
    {
        $this->validateData($data, [
            'assessment_id' => ['required', 'uuid', Rule::exists('assessments', 'id'), Rule::unique('reports', 'assessment_id')],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'comment' => ['nullable', 'string'],
        ]);
    }

    /**
     * @param array<string, mixed> $data
     * @throws ValidationException
     */
    private function validateUpdateReportData(array $data, string $id): void
    {
        $this->validateData($data, [
            'assessment_id' => ['sometimes', 'required', 'uuid', Rule::exists('assessments', 'id'), Rule::unique('reports', 'assessment_id')->ignore($id)],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
            'comment' => ['sometimes', 'nullable', 'string'],
        ]);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, array<int, mixed>> $rules
     * @throws ValidationException
     */
    private function validateData(array $data, array $rules): void
    {
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * @return array<int, string>
     */
    private function userRoleValues(): array
    {
        return array_map(
            static fn (UserRole $role): string => $role->value,
            UserRole::cases(),
        );
    }

    /**
     * @return array<int, string>
     */
    private function riskClassificationValues(): array
    {
        return array_map(
            static fn (RiskClassification $classification): string => $classification->value,
            RiskClassification::cases(),
        );
    }
}
