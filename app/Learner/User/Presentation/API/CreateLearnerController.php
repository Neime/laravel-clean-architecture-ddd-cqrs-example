<?php

declare(strict_types=1);

namespace App\Learner\User\Presentation\API;

use App\Learner\User\Application\CreateLearner\CreateLearnerCommand;
use App\Learner\User\Application\CreateLearner\LearnerAlreadyExistException;
use App\Shared\Application\CommandBus;
use App\Shared\Infrastructure\Eloquent\EloquentUser;
use App\Shared\Infrastructure\Laravel\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class CreateLearnerController extends Controller
{
    public function __construct(
        private readonly CommandBus $commandBus
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->json()->all();

        $id = Str::uuid()->toString();
        $email = $data['email'] ?? '';
        $plainPassword = $data['password'] ?? '';
        $firstname = $data['firstname'] ?? '';
        $lastname = $data['lastname'] ?? '';

        try {
            $command = new CreateLearnerCommand($id, $email, $plainPassword, $firstname, $lastname);

            $this->commandBus->handle($command);
            echo($id);
            return response()->json(EloquentUser::find($id), Response::HTTP_CREATED);
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['error' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (LearnerAlreadyExistException $exception) {
            return response()->json(['error' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}