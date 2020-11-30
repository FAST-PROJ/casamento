#!/usr/bin/env python
# coding: utf-8

import math
import operator

features = {
    'User 1': {
        'Escolaridade' : 0.5776576491728649,
        'Etinia' : 0.6047959402892891,
        'Regiao' : 0.6055689603699992,
        'Renda' : 0.4250974866145632,
        'Filho' : 0.36471612937220166,
        'classificacao' : IMPROVAVEL
    },
    'User 2': {
        'Escolaridade' : 0.2776576491728649,
        'Etinia' : 0.8047959402892891,
        'Regiao' : 0.1055689603699992,
        'Renda' : 0.6250974866145632,
        'Filho' : 0.96471612937220166,
        'classificacao' : PROVAVEL
    },
    'User 3': {
        'Escolaridade' : 0.8776576491728649,
        'Etinia' : 0.9047959402892891,
        'Regiao' : 0.5055689603699992,
        'Renda' : 0.2250974866145632,
        'Filho' : 0.76471612937220166,
        'classificacao' : PROVAVEL
    }
}

class WeddingFinder():
    def __init__(self, features):
        self.features = features

    '''
        Retorna os usuários similares
    '''
    def getCommonUsers(self, firstUser, secondUser):
        return [feature for feature in self.features[firstUser] if feature in self.features[secondUser]]

    '''
        Retorna as features de dados do usuário
    '''
    def getFeatures(self, firstUser, secondUser):
        return [
                (self.features[firstUser][feature], self.features[secondUser][feature])
                for feature in self.getCommonUsers(firstUser, secondUser)
            ]

    '''
        Obtém os pontos de distância euclidiana
    '''
    def euclideanSimilarity(self, points):
        return 1 / (1 + math.sqrt(sum([pow(point[0] - point[1], 2) for point in points])))

    '''
        Retorna a similaridade entre as features dos usuário
    '''
    def getFeatureSimilarity(self, firstUser, secondUser):
        return self.euclideanSimilarity(self.getFeatures(firstUser, secondUser))

    '''
        Faz o filtro do dataset de casamento
    '''
    def weddingFilter(self, user, num_suggestions = 5):
        similarity_scores = [
            (self.getFeatureSimilarity(user, other), other) for other in self.features if other != user
        ]

        # Buscando as pontuações da similaridade de todos os usuários
        similarity_scores.sort()
        similarity_scores.reverse()
        similarity_scores = similarity_scores[0:num_suggestions]

        recommendations = {}
        for similarity, other in similarity_scores:

            # Armazenando as avaliações
            reviewed = self.features[other]

            for feature in reviewed:
                if feature not in self.features[user]:

                    # Calculando o peso e a similaridade entre as avaliações
                    weight = similarity * reviewed[feature]

                    if feature in recommendations:
                        sim, weights = recommendations[feature]
                        # Similaridade do artigo junto com o peso
                        recommendations[feature] = (sim + similarity, weights + [weight])
                    else:
                        recommendations[feature] = (similarity, [weight])


        for recommendation in recommendations:
            similarity, feature = recommendations[recommendation]
            # Normalização das recomendações com a similaridade
            recommendations[recommendation] = sum(feature) / similarity

        # Ordenando as recomendação pelo peso
        return sorted(recommendations.items(), key=operator.itemgetter(1), reverse=True)

    '''
        Faz a recomendação dos usuários do dataset de casamento
    '''
    def weddingRecommender(self, recommendations):
        return [features[feature[0]] for features in recommendations]


'''
    Treinamento dos dados de casamento
'''
filter = WeddingFinder(features)

'''
    Testing
'''
# Retorna os artigos em comum entre os usuarios
print(filter.getCommonUsers('User 4', 'User 1'))

# Retorna os reviews em comum entre os usuarios
print(filter.getFeatures('User 4','User 1'))

# Retorna a similaridade dos reviews entre os usuarios
print(filter.getFeatureSimilarity('User 4', 'User 1'))

# Faz a recomendação dos artigos para o usuario
recommendation = filter.weddingFilter('User 4')

# Retorna os dados dos artigos recomendados
filter.weddingRecommender(recommendation)
