<?php

namespace Database\Seeders;

use App\Models\SurveyDomain;
use App\Models\SurveyItem;
use Illuminate\Database\Seeder;

class SurveyItemSeeder extends Seeder
{
    public function run(): void
    {
        $domainsData = [
            [
                'code'        => 'M',
                'name'        => 'Conceptual Metaphors',
                'color'       => '#8B5E83',
                'description' => 'Understanding programming through analogical mapping to familiar real-world concepts.',
                'sort_order'  => 1,
                'items' => [
                    'Using a box as a metaphor for a variable helps me understand how data is stored in programming.',
                    'Thinking of a function as a recipe (with ingredients and steps) makes it easier for me to understand how code works.',
                    'I can understand loops better when I think of them as repeated daily routines (e.g., brushing teeth every morning).',
                    'Imagining data structures as real-world containers (e.g., a stack of books) helps me grasp how they work.',
                    'Using metaphors like \'traffic lights\' for conditional statements helps me understand decision-making in code.',
                    'I find it easier to understand recursion when I think of it as a mirror reflecting another mirror.',
                    'Metaphors connecting programming to physical objects help me retain programming concepts longer.',
                    'Thinking of an array as a row of mailboxes helps me understand indexed data access.',
                    'I understand object-oriented programming better when I think of classes as blueprints and objects as houses.',
                    'Metaphorical thinking about abstract programming concepts reduces my cognitive load when coding.',
                ],
            ],
            [
                'code'        => 'R',
                'name'        => 'Robotics-Based Learning',
                'color'       => '#C84B31',
                'description' => 'Experiential learning through physical computing and robotics that makes abstract logic tangible.',
                'sort_order'  => 2,
                'items' => [
                    'Using physical robots (e.g., Arduino, micro:bit) helps me understand programming logic more concretely.',
                    'I understand control flow better when I can see a robot physically responding to my code.',
                    'Programming a robot to perform tasks helped me grasp the concept of functions and procedures.',
                    'Robotics activities make abstract programming concepts like loops and conditions feel tangible.',
                    'Seeing immediate physical feedback from a robot helps me debug my programs more effectively.',
                    'Using robotics tools, I better understand how input/output operations work in programming.',
                    'Robotics-based projects improved my ability to break complex problems into smaller programming steps.',
                    'I can understand sensor data and variables better through hands-on robotics experiments.',
                    'Working with physical computing devices (e.g., Raspberry Pi) has deepened my understanding of hardware-software interaction.',
                    'Robotics activities have improved my ability to apply programming concepts in real-world scenarios.',
                ],
            ],
            [
                'code'        => 'P',
                'name'        => 'Prototype Theory',
                'color'       => '#6B8E4E',
                'description' => 'Building abstract understanding from concrete prototypical examples and everyday analogies.',
                'sort_order'  => 3,
                'items' => [
                    'I understand programming loops better by thinking about everyday repetitive actions (e.g., a factory assembly line).',
                    'Relating \'if-else\' statements to real-life decisions (e.g., \'if it rains, take an umbrella\') helps me understand conditionals.',
                    'Using familiar everyday examples as prototypes helps me build my understanding of abstract programming concepts.',
                    'I can better understand data types by thinking about real objects (e.g., a number vs. a name in a list).',
                    'Prototype examples make it easier for me to generalise programming concepts to new situations.',
                    'Understanding a basic \'prototype\' program (e.g., a simple calculator) helps me comprehend more complex programs.',
                    'I learn programming concepts more effectively when introduced with a concrete example before the abstract definition.',
                    'Relating programming patterns to real-world prototypes (e.g., a to-do list for arrays) aids my understanding.',
                    'I can understand object-oriented concepts better when I think of real-world categories (e.g., \'animal\' has sub-types).',
                    'Starting with typical, easy-to-understand examples before complex cases helps me build a solid conceptual foundation.',
                ],
            ],
            [
                'code'        => 'T',
                'name'        => 'Programming Tools',
                'color'       => '#2D4A53',
                'description' => 'Using software tools, IDEs, visualisers, and interactive platforms to deepen conceptual understanding.',
                'sort_order'  => 4,
                'items' => [
                    'Using visual block-based programming tools (e.g., Scratch) helped me understand programming logic before text-based coding.',
                    'Debugging tools in my IDE (e.g., breakpoints, variable inspection) help me understand how my program executes step by step.',
                    'Version control systems (e.g., Git) have helped me understand the importance of tracking code changes.',
                    'Using an Integrated Development Environment (IDE) with code completion features helps me understand available functions and methods.',
                    'Interactive coding environments (e.g., Jupyter Notebooks) make it easier to test and understand small code snippets.',
                    'Flowchart and pseudocode tools help me plan and understand the logic of my programs before writing code.',
                    'Code visualisation tools (e.g., Python Tutor) have improved my understanding of how data and variables change during execution.',
                    'Using simulation tools helps me understand the behaviour of algorithms without running actual hardware.',
                    'Code review tools help me understand best practices and improve my programming conceptual understanding.',
                    'Online coding platforms (e.g., repl.it, CodePen) have made it easier for me to experiment with programming concepts.',
                ],
            ],
            [
                'code'        => 'O',
                'name'        => 'Ontology-Based Approaches',
                'color'       => '#D89E2F',
                'description' => 'Structuring programming knowledge hierarchically to reveal relationships and support transfer.',
                'sort_order'  => 5,
                'items' => [
                    'Organising programming concepts in a hierarchical structure (e.g., data types → primitive → integer) helps me understand their relationships.',
                    'Understanding how programming concepts relate to each other (e.g., inheritance, polymorphism) helps me learn OOP more deeply.',
                    'Creating mind maps or concept maps of programming topics helps me see the connections between different concepts.',
                    'I understand programming languages better when I learn about their shared concepts (e.g., all languages have variables and loops).',
                    'Categorising programming errors into types (e.g., syntax, runtime, logic) helps me understand and fix bugs more effectively.',
                    'Learning programming through a structured taxonomy of concepts (from basic to advanced) helps me build knowledge systematically.',
                    'Understanding how algorithms are classified (e.g., sorting, searching) helps me choose the right approach for a problem.',
                    'Knowing the ontological relationships between data structures (e.g., stack is a type of list) deepens my programming understanding.',
                    'Organising what I know about programming concepts into a knowledge framework helps me apply them more effectively.',
                    'Learning about the conceptual hierarchy of programming paradigms (procedural, OOP, functional) has broadened my programming perspective.',
                ],
            ],
        ];

        $sortOrder = 1;
        foreach ($domainsData as $domainData) {
            $domain = SurveyDomain::firstOrCreate(
                ['code' => $domainData['code']],
                [
                    'name'        => $domainData['name'],
                    'color'       => $domainData['color'],
                    'description' => $domainData['description'],
                    'sort_order'  => $domainData['sort_order'],
                ]
            );

            foreach ($domainData['items'] as $index => $statement) {
                $itemNum  = str_pad($index + 1, 2, '0', STR_PAD_LEFT);
                $itemCode = $domainData['code'] . $itemNum;

                SurveyItem::firstOrCreate(
                    ['item_code' => $itemCode],
                    [
                        'domain_id'  => $domain->id,
                        'statement'  => $statement,
                        'sort_order' => $sortOrder++,
                        'is_active'  => true,
                    ]
                );
            }
        }
    }
}
